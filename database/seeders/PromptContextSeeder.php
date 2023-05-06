<?php

namespace Database\Seeders;

use App\Models\Prompt;
use Illuminate\Database\Seeder;
use App\Models\PromptContext;

class PromptContextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PromptContext::create(config('contexts.events_creation.events_creation_v01'));
        PromptContext::create(config('contexts.events_creation.events_creation_v02')); //gpt4 first try
        PromptContext::create(config('contexts.location_details.location_details_v02'));
        PromptContext::create(config('contexts.location_details.location_details_v03')); //gpt4 first try
    }
}
