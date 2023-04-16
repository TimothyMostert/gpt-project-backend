<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventType;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $eventTypes = ['travel', 'location'];

        foreach ($eventTypes as $eventType) {
            EventType::create([
                'name' => $eventType,
            ]);
        }
    }
}
