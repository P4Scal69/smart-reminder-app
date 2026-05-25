<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'supabase_user_id' => 'nullable|uuid|unique:users,supabase_user_id',
        ]);

        // Log DB connection info for diagnostics
        try {
            $defaultConn = DB::getDefaultConnection();
            $connConf = config('database.connections.' . $defaultConn, []);
            Log::info('Register: DB connection', [
                'connection' => $defaultConn,
                'host' => $connConf['host'] ?? null,
                'database' => $connConf['database'] ?? null,
                'sslmode' => $connConf['sslmode'] ?? null,
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'supabase_user_id' => $validated['supabase_user_id'] ?? null,
            ]);

            // Create API token
            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('Register: user created', ['id' => $user->id, 'email' => $user->email]);
        } catch (\Throwable $e) {
            Log::error('Register: failed to create user', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'supabase_user_id' => 'nullable|uuid',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $incomingSupabaseUserId = $request->input('supabase_user_id');
        if ($incomingSupabaseUserId) {
            if (!$user->supabase_user_id) {
                $user->forceFill(['supabase_user_id' => $incomingSupabaseUserId])->save();
            } elseif ($user->supabase_user_id !== $incomingSupabaseUserId) {
                throw ValidationException::withMessages([
                    'email' => ['Account identity mismatch. Please use the correct account.'],
                ]);
            }
        }

        // Revoke all existing tokens (optional - for single device login)
        // $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens).
     */
    public function logoutAll(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully'
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load(['locations', 'reminders']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        $request->user()->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $request->user()
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($validated['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        if (Hash::check($validated['password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['New password must be different from current password.'],
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($validated['password'])
        ]);

        // Optionally revoke all tokens
        // $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Delete authenticated user account.
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $supabaseDeleteError = null;

        if (!$this->deleteSupabaseAuthUser($user)) {
            $supabaseDeleteError = 'Unable to delete Supabase auth user. Account deletion aborted.';
        }

        if ($supabaseDeleteError) {
            return response()->json([
                'success' => false,
                'message' => $supabaseDeleteError,
            ], 500);
        }

        DB::transaction(function () use ($user) {
            // Revoke all tokens before deletion.
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

    private function deleteSupabaseAuthUser(User $user): bool
    {
        $supabaseUrl = rtrim((string) config('services.supabase.url'), '/');
        $serviceRoleKey = (string) config('services.supabase.service_role_key');

        if (!$supabaseUrl || !$serviceRoleKey) {
            Log::error('Delete account: Supabase service config missing.');
            return false;
        }

        $supabaseUserId = $user->supabase_user_id ?: $this->findSupabaseUserIdByEmail($user->email, $supabaseUrl, $serviceRoleKey);

        if (!$supabaseUserId) {
            Log::error('Delete account: Supabase user id not found', ['email' => $user->email]);
            return false;
        }

        $response = Http::withHeaders([
            'apikey' => $serviceRoleKey,
            'Authorization' => 'Bearer ' . $serviceRoleKey,
        ])->delete($supabaseUrl . '/auth/v1/admin/users/' . $supabaseUserId);

        if (!$response->successful()) {
            Log::error('Delete account: Supabase delete failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'supabase_user_id' => $supabaseUserId,
                'email' => $user->email,
            ]);

            return false;
        }

        if ($user->supabase_user_id !== $supabaseUserId) {
            $user->forceFill(['supabase_user_id' => $supabaseUserId])->save();
        }

        return true;
    }

    private function findSupabaseUserIdByEmail(string $email, string $supabaseUrl, string $serviceRoleKey): ?string
    {
        $page = 1;

        while ($page <= 10) {
            $response = Http::withHeaders([
                'apikey' => $serviceRoleKey,
                'Authorization' => 'Bearer ' . $serviceRoleKey,
            ])->get($supabaseUrl . '/auth/v1/admin/users', [
                'page' => $page,
                'per_page' => 200,
            ]);

            if (!$response->successful()) {
                Log::error('Delete account: failed to list Supabase users', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'email' => $email,
                ]);
                return null;
            }

            $payload = $response->json();
            $users = is_array($payload['users'] ?? null) ? $payload['users'] : [];

            foreach ($users as $supabaseUser) {
                if (($supabaseUser['email'] ?? null) === $email) {
                    return $supabaseUser['id'] ?? null;
                }
            }

            if (count($users) < 200) {
                break;
            }

            $page++;
        }

        return null;
    }
}
