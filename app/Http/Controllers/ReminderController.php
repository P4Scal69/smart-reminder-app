<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    /**
     * Display a listing of the user's reminders.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->reminders()
            ->with('location');

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by location
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $reminders = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $reminders
        ]);
    }

    /**
     * Store a newly created reminder in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'nullable|in:entry,exit',
            'is_active' => 'nullable|boolean',
        ]);

        // Verify location belongs to user
        $location = Location::findOrFail($validated['location_id']);
        if ($location->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Location does not belong to you'
            ], 403);
        }

        $reminder = Auth::user()->reminders()->create([
            'location_id' => $validated['location_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'trigger_type' => $validated['trigger_type'] ?? 'entry',
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $reminder->load('location');

        return response()->json([
            'success' => true,
            'message' => 'Reminder created successfully',
            'data' => $reminder
        ], 201);
    }

    /**
     * Display the specified reminder.
     */
    public function show(Reminder $reminder)
    {
        // Ensure user owns this reminder
        if ($reminder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $reminder->load('location');

        return response()->json([
            'success' => true,
            'data' => $reminder
        ]);
    }

    /**
     * Update the specified reminder in storage.
     */
    public function update(Request $request, Reminder $reminder)
    {
        // Ensure user owns this reminder
        if ($reminder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'location_id' => 'sometimes|required|exists:locations,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'nullable|in:entry,exit',
            'is_active' => 'nullable|boolean',
        ]);

        // If location is being changed, verify it belongs to user
        if (isset($validated['location_id'])) {
            $location = Location::findOrFail($validated['location_id']);
            if ($location->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Location does not belong to you'
                ], 403);
            }
        }

        $reminder->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Reminder updated successfully',
            'data' => $reminder->fresh('location')
        ]);
    }

    /**
     * Remove the specified reminder from storage.
     */
    public function destroy(Reminder $reminder)
    {
        // Ensure user owns this reminder
        if ($reminder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $reminder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reminder deleted successfully'
        ]);
    }

    /**
     * Toggle reminder active status.
     */
    public function toggle(Reminder $reminder)
    {
        // Ensure user owns this reminder
        if ($reminder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $reminder->update([
            'is_active' => !$reminder->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reminder status toggled',
            'data' => $reminder->fresh()
        ]);
    }

    /**
     * Trigger a reminder manually (for testing).
     */
    public function trigger(Reminder $reminder)
    {
        // Ensure user owns this reminder
        if ($reminder->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $reminder->recordTrigger();

        return response()->json([
            'success' => true,
            'message' => 'Reminder triggered',
            'data' => $reminder->fresh()
        ]);
    }

    /**
     * Get active reminders for current user's current location.
     */
    public function activeByLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        // Find locations containing this point
        $locations = Auth::user()->locations()
            ->whereContains('geofence_area', new \MatanYadaev\EloquentSpatial\Objects\Point(
                $validated['longitude'],
                $validated['latitude']
            ))
            ->pluck('id');

        // Get active reminders for those locations
        $reminders = Auth::user()->reminders()
            ->whereIn('location_id', $locations)
            ->where('is_active', true)
            ->with('location')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reminders,
            'count' => $reminders->count()
        ]);
    }
}
