<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use App\Models\Reminder;
use Illuminate\Database\Seeder;

class ReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::where('email', 'john@example.com')->first();
        $user2 = User::where('email', 'jane@example.com')->first();

        if (!$user1 || !$user2) {
            echo "\n⚠️  Users not found. Run LocationSeeder first.\n";
            return;
        }

        // User 1 Reminders
        $homeLocation = $user1->locations()->where('name', 'Home')->first();
        if ($homeLocation) {
            $user1->reminders()->create([
                'location_id' => $homeLocation->id,
                'title' => 'Water the plants',
                'description' => 'Water all plants in the living room and balcony',
                'is_active' => true,
            ]);

            $user1->reminders()->create([
                'location_id' => $homeLocation->id,
                'title' => 'Turn off AC',
                'description' => 'Remember to turn off AC when leaving',
                'is_active' => true,
            ]);
        }

        $officeLocation = $user1->locations()->where('name', 'Office')->first();
        if ($officeLocation) {
            $user1->reminders()->create([
                'location_id' => $officeLocation->id,
                'title' => 'Check emails',
                'description' => 'Review and respond to urgent emails',
                'is_active' => true,
            ]);

            $user1->reminders()->create([
                'location_id' => $officeLocation->id,
                'title' => 'Attend standup meeting',
                'description' => 'Daily standup at 9:30 AM',
                'is_active' => true,
            ]);
        }

        $gymLocation = $user1->locations()->where('name', 'Gym')->first();
        if ($gymLocation) {
            $user1->reminders()->create([
                'location_id' => $gymLocation->id,
                'title' => 'Leg day workout',
                'description' => 'Squats, lunges, and leg press',
                'is_active' => true,
            ]);
        }

        $supermarketLocation = $user1->locations()->where('name', 'Supermarket')->first();
        if ($supermarketLocation) {
            $user1->reminders()->create([
                'location_id' => $supermarketLocation->id,
                'title' => 'Buy groceries',
                'description' => 'Milk, bread, eggs, vegetables, and fruits',
                'is_active' => true,
            ]);

            $user1->reminders()->create([
                'location_id' => $supermarketLocation->id,
                'title' => 'Get cleaning supplies',
                'description' => 'Detergent, dish soap, sponges',
                'is_active' => false,
            ]);
        }

        $parentsLocation = $user1->locations()->where('name', 'Parents House')->first();
        if ($parentsLocation) {
            $user1->reminders()->create([
                'location_id' => $parentsLocation->id,
                'title' => 'Call mom',
                'description' => 'Check in with family',
                'is_active' => true,
            ]);
        }

        // User 2 Reminders
        $homeLocation2 = $user2->locations()->where('name', 'Home')->first();
        if ($homeLocation2) {
            $user2->reminders()->create([
                'location_id' => $homeLocation2->id,
                'title' => 'Study for exam',
                'description' => 'Review chapters 5-8',
                'is_active' => true,
            ]);
        }

        $campusLocation = $user2->locations()->where('name', 'Campus')->first();
        if ($campusLocation) {
            $user2->reminders()->create([
                'location_id' => $campusLocation->id,
                'title' => 'Submit assignment',
                'description' => 'Final project deadline today',
                'is_active' => true,
            ]);

            $user2->reminders()->create([
                'location_id' => $campusLocation->id,
                'title' => 'Meet with advisor',
                'description' => 'Discuss thesis progress',
                'is_active' => true,
            ]);
        }

        $coffeeShopLocation = $user2->locations()->where('name', 'Coffee Shop')->first();
        if ($coffeeShopLocation) {
            $user2->reminders()->create([
                'location_id' => $coffeeShopLocation->id,
                'title' => 'Work on presentation',
                'description' => 'Prepare slides for group project',
                'is_active' => true,
            ]);
        }

        $libraryLocation = $user2->locations()->where('name', 'Library')->first();
        if ($libraryLocation) {
            $user2->reminders()->create([
                'location_id' => $libraryLocation->id,
                'title' => 'Return books',
                'description' => 'Return borrowed books before due date',
                'is_active' => true,
            ]);

            $user2->reminders()->create([
                'location_id' => $libraryLocation->id,
                'title' => 'Research for thesis',
                'description' => 'Find references in journal database',
                'is_active' => false,
            ]);
        }

        echo "\n✅ Created 14 reminders\n";
        echo "   User 1 (John): 8 reminders\n";
        echo "   User 2 (Jane): 6 reminders\n\n";
    }
}
