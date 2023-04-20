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

    public function createEventsContext($prompt, $interests, $promptContext)
    {
        $context = $promptContext->context;

        $interests = implode(', ', $interests);

        $revisedContext = [];

        foreach ($context as $step) {
            $step['content'] = str_replace('<<prompt>>', $prompt, $step['content']);
            $step['content'] = str_replace('<<interests>>', $interests, $step['content']);
            $revisedContext[] = $step;
        }

        return $revisedContext;
    }

    public function createEventDetailsContext($uuid, $itinerary, $promptContext)
    {
        $context = $promptContext->context;

        $compressedEvents = $this->compressEvents($itinerary->events);

        $revisedContext = [];

        foreach ($context as $step) {
            $step['content'] = str_replace('<<uuid>>', $uuid, $step['content']);
            $step['content'] = str_replace('<<events>>', $compressedEvents, $step['content']);
            $revisedContext[] = $step;
        }

        return $revisedContext;
    }

    public function extractJson($string)
    {
        // Find the starting and ending positions of the JSON object
        $json_start = strpos($string, '{');
        $json_end = strrpos($string, '}');

        // Extract the JSON object from the string
        $json_string = substr($string, $json_start, $json_end - $json_start + 1);

        $json_string = json_decode($json_string);

        // Return the extracted JSON
        return $json_string;
    }

    public function extractEvents($response)
    {
        // Remove any extra text before the compressed itinerary
        $compressedItinerary = strstr($response, 'u1|');

        // Split the itinerary into lines
        $lines = explode("\n", $compressedItinerary);

        // Initialize an empty events array
        $events = [];

        // Process each line
        foreach ($lines as $line) {
            // Skip empty lines
            if (trim($line) === '') {
                continue;
            }

            // Split the line into parts using the '|' separator
            $parts = explode('|', $line);

            // Create an event array from the parts and add it to the events array
            $events[] = [
                'uuid' => trim($parts[0]),
                'title' => trim($parts[1]),
                'location' => trim($parts[2]),
            ];
        }

        return $events;
    }

    public function compressEvents($events)
    {
        // Initialize an empty compressed itinerary string
        $compressedItinerary = '';

        // Process each event in the input JSON
        foreach ($events as $event) {
            // Extract the uuid, title, and location from the event
            $uuid = $event->uuid;
            $title = $event->locationEvent->title;
            $location = $event->locationEvent->location->name;

            // Combine the extracted fields into a single line using '|' separator
            $compressedEvent = "{$uuid}|{$title}|{$location}";

            // Add the compressed event line to the compressed itinerary string, followed by a newline character
            $compressedItinerary .= $compressedEvent . "\n";
        }

        return $compressedItinerary;
    }

    public function extractLocationEvent($response)
    {
        $lines = explode("\n", $response);
        $event = [
            'uuid' => '',
            'description' => '',
            'type' => ''
        ];
        $activities = [];

        $eventRegex = '/^\s*u(\d+)\|([^|]+)\|([^|]+)\s*$/';
        $activityRegex = '/^\s*a(\d+)\|([^|]+)\|([^|]+)\|([^|]+)\s*$/';

        foreach ($lines as $line) {
            error_log($line);
            if (preg_match($eventRegex, $line, $matches)) {
                $event = [
                    'uuid' => 'u' . $matches[1],
                    'description' => $matches[2],
                    'type' => $matches[3]
                ];
            } elseif (preg_match($activityRegex, $line, $matches)) {
                $activities[] = [
                    'uuid' => 'a' . $matches[1],
                    'title' => $matches[2],
                    'description' => $matches[3],
                    'category' => $matches[4]
                ];
            }
        }

        $locationEvent = [
            'event' => $event,
            'activities' => $activities
        ];

        return $locationEvent;
    }
}
