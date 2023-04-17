<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI as OpenAI;

class OpenaiAPIService
{
    public function basicPrompt($prompt, $max_tokens = 2000, $model = 'gpt-3.5-turbo', $temperature = 0.5)
    {
        $result = OpenAI::completions()->create([
            'model' => $model,
            'prompt' => $prompt,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
        ]);

        return $result;
    }

    public function contextualPrompt($context, $max_tokens = 2000, $model = 'gpt-3.5-turbo', $temperature = 0.5)
    {
        $result = OpenAI::chat()->create([
            'model' => $model,
            'messages' => $context,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
        ]);

        return $result;
    }

    public function createRandomPrompt($max_tokens = 100, $model = 'gpt-3.5-turbo', $temperature = 0.5)
    {
        $messages = [
            [
                'role'  => 'user',
                'content' => "
                        Give me a travel prompt in the style of these examples:
                        'Create a culinary adventure through Italy, focusing on unique regional dishes and local cooking classes.'
                        'Design a wildlife safari itinerary in Tanzania, with a mix of game drives, birdwatching, and cultural experiences.'
                        'Craft a sustainable eco-tour itinerary in Costa Rica, featuring eco-lodges, rainforest hikes, and wildlife conservation projects.'
                        'Build a road trip itinerary along the scenic Pacific Coast Highway, including must-see attractions, local eateries, and hidden gems.'
                        'Develop an itinerary exploring Japan's ancient history and cultural heritage, with visits to temples, shrines, and traditional arts performances.'
                        'Construct an adventure itinerary in New Zealand, featuring adrenaline-pumping activities like bungee jumping, skydiving, and glacier hiking.'
                        'Curate an itinerary for a solo traveler exploring Scandinavia's natural beauty and vibrant city life in Norway, Sweden, and Denmark.'
                        'Organize a literary-themed journey through the United Kingdom, with visits to famous authors' homes, iconic book locations, and literary museums.'
                     ",
            ],
        ];
        $result = OpenAI::chat()->create([
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
        ]);

        return $result;
    }

    public function moderateInput($input)
    {
        $response = OpenAI::moderations()->create([
            'model' => 'text-moderation-latest',
            'input' => $input,
        ]);

        $flagged = false;

        foreach ($response->results as $result) {
            $result->flagged; // true

            foreach ($result->categories as $category) {
                $category->category->value; // 'violence'
                $category->violated; // true
                $category->score; // 0.97431367635727
            }
        }

        return $flagged;
    }
}
