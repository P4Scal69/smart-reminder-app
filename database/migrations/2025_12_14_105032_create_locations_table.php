<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $transaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isPgsql = DB::connection()->getDriverName() === 'pgsql';
        $hasPostgis = false;

        if ($isPgsql) {
            try {
                DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
                $hasPostgis = true;
            } catch (\Throwable $e) {
                // PostGIS not available on this server; spatial features will be skipped
            }
        }

        Schema::create('locations', function (Blueprint $table) use ($isPgsql) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('geofence_radius')->default(100); // in meters
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        if ($isPgsql && $hasPostgis) {
            try {
                DB::statement('ALTER TABLE locations ADD COLUMN point GEOMETRY(POINT, 4326)');
                DB::statement('ALTER TABLE locations ADD COLUMN geofence_area GEOMETRY(POLYGON, 4326)');

                DB::statement('CREATE INDEX locations_point_idx ON locations USING GIST (point)');
                DB::statement('CREATE INDEX locations_geofence_area_idx ON locations USING GIST (geofence_area)');
            } catch (\Throwable $e) {
                // Spatial columns/indexes could not be created
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
        // PostGIS extension will remain as other tables might use it
    }
};
