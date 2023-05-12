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
        // VERSION !
        PromptContext::create([
            'name' => 'events_1',
            'description' => 'Version 1 of the fullflow events prompt context for use with gpt4, focused on consistency and brevity.',
            'context' => config('contexts.full_gpt4_v1.events')
        ]);
        PromptContext::create([
            'name' => 'details_1',
            'description' => 'Version 1 of the fullflow details prompt context for use with gpt4, focused on consistency and brevity.',
            'context' => config('contexts.full_gpt4_v1.details')
        ]);
        PromptContext::create([
            'name' => 'edit_1',
            'description' => 'A more concise edit event prompt context for use with gpt4.',
            'context' => config('contexts.full_gpt4_v1.edit')
        ]);
        PromptContext::create([
            'name' => 'add_1',
            'description' => 'A more concise add event prompt context for use with gpt4.',
            'context' => config('contexts.full_gpt4_v1.add')
        ]);
    }
}
