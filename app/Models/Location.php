<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Location extends Model
{
    use HasSpatial;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'geofence_radius',
        'point',
        'geofence_area',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'geofence_radius' => 'integer',
        'point' => Point::class,
        'geofence_area' => Polygon::class,
    ];

    /**
     * Get the user that owns the location.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reminders for the location.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Get active reminders for the location.
     */
    public function activeReminders(): HasMany
    {
        return $this->hasMany(Reminder::class)->where('is_active', true);
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically create Point and Polygon geometry when saving
        static::saving(function ($location) {
            if ($location->latitude && $location->longitude) {
                // Create Point geometry from lat/lng
                $location->point = new Point($location->latitude, $location->longitude, 4326);
                
                // Create circular Polygon geometry for geofence
                if ($location->geofence_radius) {
                    $location->geofence_area = self::createCirclePolygon(
                        $location->latitude,
                        $location->longitude,
                        $location->geofence_radius
                    );
                }
            }
        });
    }

    /**
     * Create a circular polygon for geofence area.
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param int $radius Radius in meters
     * @return Polygon
     */
    private static function createCirclePolygon(float $lat, float $lng, int $radius): Polygon
    {
        $points = [];
        $segments = 36; // Number of segments to approximate circle
        
        // Earth's radius in meters
        $earthRadius = 6371000;
        
        // Convert radius to degrees
        $radiusLat = ($radius / $earthRadius) * (180 / pi());
        $radiusLng = $radiusLat / cos(deg2rad($lat));
        
        // Create circle points
        for ($i = 0; $i <= $segments; $i++) {
            $angle = ($i * 360 / $segments) * (pi() / 180);
            $pointLat = $lat + ($radiusLat * sin($angle));
            $pointLng = $lng + ($radiusLng * cos($angle));
            $points[] = new Point($pointLat, $pointLng, 4326);
        }
        
        return new Polygon([$points], 4326);
    }

    /**
     * Check if a given point is within the geofence.
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return bool
     */
    public function isWithinGeofence(float $lat, float $lng): bool
    {
        $point = new Point($lat, $lng, 4326);
        
        // Use PostGIS ST_DWithin for distance-based check
        return static::query()
            ->whereKey($this->id)
            ->whereRaw('ST_DWithin(point::geography, ST_GeomFromText(?, 4326)::geography, ?)', [
                $point->toWkt(),
                $this->geofence_radius
            ])
            ->exists();
    }

    /**
     * Scope to find locations within a certain distance from a point.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param int $distance Distance in meters
     */
    public function scopeWithinDistance($query, float $lat, float $lng, int $distance)
    {
        $point = new Point($lat, $lng, 4326);
        
        return $query->whereRaw('ST_DWithin(point::geography, ST_GeomFromText(?, 4326)::geography, ?)', [
            $point->toWkt(),
            $distance
        ]);
    }
}
