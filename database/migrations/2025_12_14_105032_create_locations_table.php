<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable PostGIS extension if not already enabled
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        
        Schema::create('locations', function (Blueprint $table) {
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
        
        // Add PostGIS geometry columns using raw SQL
        DB::statement('ALTER TABLE locations ADD COLUMN point GEOMETRY(POINT, 4326)');
        DB::statement('ALTER TABLE locations ADD COLUMN geofence_area GEOMETRY(POLYGON, 4326)');
        
        // Create spatial indexes for better query performance
        DB::statement('CREATE INDEX locations_point_idx ON locations USING GIST (point)');
        DB::statement('CREATE INDEX locations_geofence_area_idx ON locations USING GIST (geofence_area)');
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
