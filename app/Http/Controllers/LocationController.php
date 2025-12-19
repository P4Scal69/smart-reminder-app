<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;

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

        // Create Point geometry
        $point = new Point($validated['longitude'], $validated['latitude']);

        // Create circular Polygon for geofence if radius is provided
        $geofenceArea = null;
        if (isset($validated['geofence_radius'])) {
            $geofenceArea = $this->createCircularPolygon(
                $validated['latitude'],
                $validated['longitude'],
                $validated['geofence_radius']
            );
        }

        $location = Auth::user()->locations()->create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'geofence_radius' => $validated['geofence_radius'] ?? 100,
            'point' => $point,
            'geofence_area' => $geofenceArea,
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

        // Update Point if coordinates changed
        if (isset($validated['latitude']) || isset($validated['longitude'])) {
            $lat = $validated['latitude'] ?? $location->latitude;
            $lng = $validated['longitude'] ?? $location->longitude;
            $validated['point'] = new Point($lng, $lat);
        }

        // Update geofence area if radius or coordinates changed
        if (isset($validated['geofence_radius']) || isset($validated['latitude']) || isset($validated['longitude'])) {
            $lat = $validated['latitude'] ?? $location->latitude;
            $lng = $validated['longitude'] ?? $location->longitude;
            $radius = $validated['geofence_radius'] ?? $location->geofence_radius;

            $validated['geofence_area'] = $this->createCircularPolygon($lat, $lng, $radius);
        }

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

        $point = new Point($validated['longitude'], $validated['latitude'], 4326);

        $locations = Auth::user()->locations()
            ->whereContains('geofence_area', $point)
            ->with('reminders')
            ->get();

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

        $point = new Point($validated['longitude'], $validated['latitude']);
        $distance = $validated['distance'] ?? 1000; // Default 1km

        $locations = Auth::user()->locations()
            ->whereDistance('point', $point, '<=', $distance)
            ->orderByDistance('point', $point)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
            'count' => $locations->count()
        ]);
    }

    /**
     * Create a circular polygon approximation for geofence.
     * 
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param int $radius Radius in meters
     * @return Polygon
     */
    private function createCircularPolygon($lat, $lng, $radius)
    {
        $points = [];
        $numberOfPoints = 32; // More points = smoother circle

        for ($i = 0; $i <= $numberOfPoints; $i++) {
            $angle = ($i / $numberOfPoints) * 2 * M_PI;
            
            // Calculate offset in degrees (approximate)
            // 1 degree latitude ≈ 111,320 meters
            // 1 degree longitude ≈ 111,320 * cos(latitude) meters
            $latOffset = ($radius * cos($angle)) / 111320;
            $lngOffset = ($radius * sin($angle)) / (111320 * cos(deg2rad($lat)));
            
            $points[] = new Point(
                $lng + $lngOffset,
                $lat + $latOffset
            );
        }

        return new Polygon([new LineString($points)]);
    }
}
