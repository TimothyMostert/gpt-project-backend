<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityType;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        // now with descriptions

        $activityTypes = [
            ['name' => 'Adventure', 'description' => 'Engage in exciting outdoor activities like hiking, biking, climbing, and more.'],
            ['name' => 'Animals', 'description' => 'Explore the fascinating world of animals at zoos, aquariums, wildlife sanctuaries, and more.'],
            ['name' => 'Architecture', 'description' => 'Discover iconic structures and impressive designs through tours, museums, and exhibitions.'],
            ['name' => 'Arts', 'description' => 'Immerse yourself in the creative scene with galleries, murals, art installations, and performances.'],
            ['name' => 'Beach', 'description' => 'Relax on sandy shores, swim in crystal-clear waters, and indulge in a variety of water sports.'],
            ['name' => 'Boat', 'description' => 'Experience sailing, cruising, or fishing with a variety of boat tours and water-based adventures.'],
            ['name' => 'Camping', 'description' => 'Unplug and reconnect with nature through tent camping, RV parks, or glamping experiences.'],
            ['name' => 'Cultural', 'description' => 'Delve into the unique customs, traditions, and heritage of a region through festivals, events, and performances.'],
            ['name' => 'Food', 'description' => 'Savor local cuisines, participate in cooking classes, and explore food markets and fine dining.'],
            ['name' => 'History', 'description' => 'Uncover the past through historical sites, monuments, museums, and guided tours.'],
            ['name' => 'Nature', 'description' => 'Escape into the beauty of the natural world through parks, gardens, reserves, and scenic trails.'],
            ['name' => 'Nightlife', 'description' => "Experience the vibrant energy of a city's nightlife with bars, clubs, live music, and entertainment."],
            ['name' => 'Shopping', 'description' => 'Indulge in retail therapy at malls, markets, boutiques, and local artisan shops.'],
            ['name' => 'Sightseeing', 'description' => 'Explore must-see attractions, landmarks, and breathtaking viewpoints in a city or region.'],
            ['name' => 'Spa', 'description' => 'Unwind and rejuvenate with massages, body treatments, and wellness therapies at luxurious spa retreats.'],
            ['name' => 'Sports', 'description' => 'Get your adrenaline pumping with spectator sports, fitness classes, or active recreational pursuits.'],
            ['name' => 'Other', 'description' => "Discover unique and off-the-beaten-path experiences that don't fit into the standard categories."]
        ];

        foreach ($activityTypes as $activityType) {
            ActivityType::create([
                'name' => $activityType['name'],
                'description' => $activityType['description'],
            ]);
        }
    }
}
