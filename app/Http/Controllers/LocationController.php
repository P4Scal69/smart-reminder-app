<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Display a listing of the user's locations.
     */
    public function index()
    {
        $locations = Auth::user()->locations()
            ->with('reminders')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius' => 'nullable|integer|min:10|max:5000',
            'notes' => 'nullable|string',
        ]);

        $location = Auth::user()->locations()->create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'geofence_radius' => $validated['geofence_radius'] ?? 100,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location created successfully',
            'data' => $location
        ], 201);
    }

    /**
     * Display the specified location.
     */
    public function show(Location $location)
    {
        // Ensure user owns this location
        if ($location->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $location->load('reminders');

        return response()->json([
            'success' => true,
            'data' => $location
        ]);
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location)
    {
        // Ensure user owns this location
        if ($location->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'geofence_radius' => 'nullable|integer|min:10|max:5000',
            'notes' => 'nullable|string',
        ]);

        $location->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => $location->fresh()
        ]);
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Location $location)
    {
        // Ensure user owns this location
        if ($location->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully'
        ]);
    }

    /**
     * Check if a given point is within any of the user's geofences.
     */
    public function checkGeofence(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $lat = $validated['latitude'];
        $lng = $validated['longitude'];

        $locations = Auth::user()->locations()
            ->select('id', 'name', 'address', 'latitude', 'longitude', 'geofence_radius', 'notes', 'created_at', 'updated_at')
            ->get()
            ->filter(function ($location) use ($lat, $lng) {
                $distance = 6371000 * acos(
                    cos(deg2rad($lat)) *
                    cos(deg2rad($location->latitude)) *
                    cos(deg2rad($location->longitude) - deg2rad($lng)) +
                    sin(deg2rad($lat)) *
                    sin(deg2rad($location->latitude))
                );

                return $distance <= ($location->geofence_radius ?? 100);
            });

        return response()->json([
            'success' => true,
            'inside_geofence' => $locations->isNotEmpty(),
            'locations' => $locations,
            'count' => $locations->count()
        ]);
    }

    /**
     * Get locations near a given point within specified distance.
     */
    public function nearby(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'distance' => 'nullable|integer|min:100|max:10000', // in meters
        ]);

        $distance = $validated['distance'] ?? 1000; // Default 1km
        $lat = $validated['latitude'];
        $lng = $validated['longitude'];

        $locations = Auth::user()->locations()
            ->select('id', 'name', 'address', 'latitude', 'longitude', 'geofence_radius')
            ->get()
            ->map(function ($location) use ($lat, $lng, $distance) {
                $dist = 6371000 * acos(
                    cos(deg2rad($lat)) *
                    cos(deg2rad($location->latitude)) *
                    cos(deg2rad($location->longitude) - deg2rad($lng)) +
                    sin(deg2rad($lat)) *
                    sin(deg2rad($location->latitude))
                );
                $location->distance = $dist;

                return $location;
            })
            ->filter(function ($location) use ($distance) {
                return $location->distance <= $distance;
            })
            ->sortBy('distance')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $locations,
            'count' => $locations->count()
        ]);
    }
}
