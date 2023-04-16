<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TravelMode;

class TravelModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $travelModes = ['Car', 'Bus', 'Train', 'Flight', 'Bike', 'Walk', 'Ferry', 'Other'];

        foreach ($travelModes as $travelMode) {
            TravelMode::create([
                'name' => $travelMode,
            ]);
        }
    }
}
