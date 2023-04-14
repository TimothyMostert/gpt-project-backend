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
}