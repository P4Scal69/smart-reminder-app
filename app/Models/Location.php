<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString; // WAJIB ADA
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Location extends Model
{
    use HasSpatial;

    protected static ?bool $postgisAvailable = null;

    protected static function postgisAvailable(): bool
    {
        if (static::$postgisAvailable !== null) {
            return static::$postgisAvailable;
        }

        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                DB::select("SELECT 1 FROM geometry_columns WHERE f_table_name = 'locations' LIMIT 1");
                static::$postgisAvailable = true;

                return true;
            }
        } catch (\Throwable $e) {
        }

        static::$postgisAvailable = false;

        return false;
    }

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
    ];

    protected static function postgisAvailable(): bool
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                DB::select("SELECT 1 FROM geometry_columns WHERE f_table_name = 'locations' LIMIT 1");

                return true;
            }
        } catch (\Throwable $e) {
        }

        return false;
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
    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($location) {
            if ($location->latitude && $location->longitude) {
                if (static::postgisAvailable()) {
                    $location->point = new Point($location->longitude, $location->latitude, 4326);
                    
                    if ($location->geofence_radius) {
                        $location->geofence_area = self::createCirclePolygon(
                            $location->latitude,
                            $location->longitude,
                            $location->geofence_radius
                        );
                    }
                }
            }
        });
    }

    /**
     * Create a circular polygon for geofence area.
     */
   private static function createCirclePolygon(float $lat, float $lng, int $radius): Polygon
{
    $points = [];
    $segments = 36;
    $earthRadius = 6371000;
    
    $radiusLat = ($radius / $earthRadius) * (180 / pi());
    $radiusLng = $radiusLat / cos(deg2rad($lat));
    
    for ($i = 0; $i <= $segments; $i++) {
        $angle = ($i * 360 / $segments) * (pi() / 180);
        $pLat = $lat + ($radiusLat * sin($angle));
        $pLng = $lng + ($radiusLng * cos($angle));
        
        // PostGIS / Spatial Library: (Longitude, Latitude)
        $points[] = new Point($pLng, $pLat, 4326);
    }
    
    // Polygon membutuhkan ARRAY dari LineString. 
    // LineString membutuhkan ARRAY dari Point.
    return new Polygon([
        new LineString($points)
    ], 4326);
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
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return static::query()
                ->whereKey($this->id)
                ->whereRaw('ST_DWithin(point::geography, ST_GeomFromText(?, 4326)::geography, ?)', [
                    $point->toWkt(),
                    $this->geofence_radius
                ])
                ->exists();
        }

        return static::query()
            ->whereKey($this->id)
            ->whereRaw(
                '(6371000 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
                [$lat, $lng, $lat, $this->geofence_radius]
            )
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
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $point = new Point($lat, $lng, 4326);

            return $query->whereRaw('ST_DWithin(point::geography, ST_GeomFromText(?, 4326)::geography, ?)', [
                $point->toWkt(),
                $distance
            ]);
        }

        return $query->whereRaw(
            '(6371000 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
            [$lat, $lng, $lat, $distance]
        );
    }
}
