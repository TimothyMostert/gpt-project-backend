<?php

namespace App\Services;

use App\Models\PromptContext;
use App\Models\ActivityType;
use App\Models\TravelMode;
use App\Models\EventType;
use Error;

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

    function extractEvents($response)
    {
        error_log(json_encode($response));

        // Find all content within square brackets
        $pattern = '/\[([^]]+)\]/';
        preg_match_all($pattern, $response, $matches);

        // Initialize an empty events array
        $events = [];

        error_log("Events output:");

        // Process each line
        foreach ($matches[1] as $line) {
            error_log($line);

            // Split the line into parts using the '|' separator
            $parts = explode('|', $line);

            // Create an event array from the parts and add it to the events array
            $events[] = [
                'uuid' => $parts[0] ?? null,
                'title' => $parts[1] ?? null,
                'location' => $parts[2] ?? null,
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
            $compressedEvent = "[{$uuid}|{$title}|{$location}]";

            // Add the compressed event line to the compressed itinerary string, followed by a newline character
            $compressedItinerary .= $compressedEvent . "\n";
        }

        return $compressedItinerary;
    }

    public function extractLocationEvent($response)
    {
        error_log(json_encode($response));

        // Remove any extra text before the compressed event
        $pattern = '/\[([^]]+)\]/';
        preg_match_all($pattern, $response, $matches);

        $event = [];
        $activities = [];

        error_log("Location event output:");

        foreach ($matches[1] as $line) {
            error_log($line);

            $data = explode('|', $line);
            $type = trim($data[0]);

            if ($type === 'e') {
                $event = [
                    'description' => $data[2],
                ];
            } elseif ($type === 'a') {
                $activities[] = [
                    'uuid' => $data[1],
                    'title' => $data[2],
                    'description' => $data[3],
                    'type' => $data[4],
                ];
            }
        }

        return [
            'event' => $event,
            'activities' => $activities,
        ];
    }
}
