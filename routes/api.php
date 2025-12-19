<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReminderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no auth required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String()
    ]);
});

// Authentication routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Diagnostic route removed

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes (protected)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Location routes
    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index']);
        Route::post('/', [LocationController::class, 'store']);
        Route::get('/{location}', [LocationController::class, 'show']);
        Route::put('/{location}', [LocationController::class, 'update']);
        Route::patch('/{location}', [LocationController::class, 'update']);
        Route::delete('/{location}', [LocationController::class, 'destroy']);
        
        // Additional location endpoints
        Route::post('/check-geofence', [LocationController::class, 'checkGeofence']);
        Route::post('/nearby', [LocationController::class, 'nearby']);
    });
    
    // Reminder routes
    Route::prefix('reminders')->group(function () {
        Route::get('/', [ReminderController::class, 'index']);
        Route::post('/', [ReminderController::class, 'store']);
        Route::get('/{reminder}', [ReminderController::class, 'show']);
        Route::put('/{reminder}', [ReminderController::class, 'update']);
        Route::patch('/{reminder}', [ReminderController::class, 'update']);
        Route::delete('/{reminder}', [ReminderController::class, 'destroy']);
        
        // Additional reminder endpoints
        Route::post('/{reminder}/toggle', [ReminderController::class, 'toggle']);
        Route::post('/{reminder}/trigger', [ReminderController::class, 'trigger']);
        Route::post('/active-by-location', [ReminderController::class, 'activeByLocation']);
    });
});
