<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MatanYadaev\EloquentSpatial\Objects\Point;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // User 1 Locations - Jakarta area
        $user1->locations()->create([
            'name' => 'Home',
            'address' => 'Menteng, Jakarta Pusat',
            'latitude' => -6.1944,
            'longitude' => 106.8229,
            'point' => new Point(106.8229, -6.1944),
            'geofence_radius' => 300,
            'notes' => 'My home sweet home',
        ]);

        $user1->locations()->create([
            'name' => 'Office',
            'address' => 'Sudirman, Jakarta Selatan',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'point' => new Point(106.8456, -6.2088),
            'geofence_radius' => 500,
            'notes' => 'Work location',
        ]);

        $user1->locations()->create([
            'name' => 'Gym',
            'address' => 'Senayan, Jakarta Selatan',
            'latitude' => -6.2291,
            'longitude' => 106.8048,
            'point' => new Point(106.8048, -6.2291),
            'geofence_radius' => 200,
            'notes' => 'Fitness center',
        ]);

        $user1->locations()->create([
            'name' => 'Supermarket',
            'address' => 'Grand Indonesia, Jakarta',
            'latitude' => -6.1944,
            'longitude' => 106.8229,
            'point' => new Point(106.8229, -6.1944),
            'geofence_radius' => 400,
            'notes' => 'Grocery shopping',
        ]);

        $user1->locations()->create([
            'name' => 'Parents House',
            'address' => 'Kelapa Gading, Jakarta Utara',
            'latitude' => -6.1582,
            'longitude' => 106.9066,
            'point' => new Point(106.9066, -6.1582),
            'geofence_radius' => 350,
            'notes' => 'Family home',
        ]);

        // User 2 Locations - Bandung area
        $user2->locations()->create([
            'name' => 'Home',
            'address' => 'Dago, Bandung',
            'latitude' => -6.8700,
            'longitude' => 107.6139,
            'point' => new Point(107.6139, -6.8700),
            'geofence_radius' => 250,
            'notes' => 'My apartment',
        ]);

        $user2->locations()->create([
            'name' => 'Campus',
            'address' => 'ITB Ganesha, Bandung',
            'latitude' => -6.8915,
            'longitude' => 107.6107,
            'point' => new Point(107.6107, -6.8915),
            'geofence_radius' => 600,
            'notes' => 'University',
        ]);

        $user2->locations()->create([
            'name' => 'Coffee Shop',
            'address' => 'Braga, Bandung',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'point' => new Point(107.6191, -6.9175),
            'geofence_radius' => 150,
            'notes' => 'Favorite cafe',
        ]);

        $user2->locations()->create([
            'name' => 'Library',
            'address' => 'Sudirman, Bandung',
            'latitude' => -6.9039,
            'longitude' => 107.6186,
            'point' => new Point(107.6186, -6.9039),
            'geofence_radius' => 300,
            'notes' => 'Study spot',
        ]);

        echo "\n✅ Created 2 users with 9 locations\n";
        echo "   User 1 (john@example.com): 5 locations in Jakarta\n";
        echo "   User 2 (jane@example.com): 4 locations in Bandung\n";
    }
}
