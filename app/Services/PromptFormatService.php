<?php

namespace App\Services;

use App\Models\PromptContext;
use App\Models\ActivityType;
use App\Models\TravelMode;
use App\Models\EventType;

class PromptFormatService
{
    public function createItineraryContext($prompt, $tags)
    {
        $promptContext = PromptContext::find(1);
        $context = $promptContext->context;

        $eventTypes = implode(', ', EventType::all()->pluck('name')->toArray());
        $travelModes = implode(', ', TravelMode::all()->pluck('name')->toArray());
        $activityTypes = implode(', ', ActivityType::all()->pluck('name')->toArray());

        $tags = implode(', ', $tags);

        $revisedContext = [];

        foreach ($context as $step) {
            $step['content'] = str_replace('<<eventTypes>>', $eventTypes, $step['content']);
            $step['content'] = str_replace('<<travelModes>>', $travelModes, $step['content']);
            $step['content'] = str_replace('<<activityTypes>>', $activityTypes, $step['content']);
            $step['content'] = str_replace('<<prompt>>', $prompt, $step['content']);
            $step['content'] = str_replace('<<tags>>', $tags, $step['content']);
            $revisedContext[] = $step;
        }

        return $revisedContext;
    }

    function extractJson($string) {
        // Find the starting and ending positions of the JSON object
        $json_start = strpos($string, '{');
        $json_end = strrpos($string, '}');
    
        // Extract the JSON object from the string
        $json_string = substr($string, $json_start, $json_end - $json_start + 1);

       $json_string = json_decode($json_string);
    
        // Return the extracted JSON
        return $json_string;
    }
}
