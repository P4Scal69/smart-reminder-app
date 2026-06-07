<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            $forwardedProto = request()->header('x-forwarded-proto');

            if ($forwardedProto === 'https') {
                URL::forceScheme('https');
            }
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            Log::warning('Running on SQLite. PostGIS spatial features (geofence queries) will fall back to Haversine formula. For full PostGIS support, use PostgreSQL with PostGIS extension.');
        }
    }
}
