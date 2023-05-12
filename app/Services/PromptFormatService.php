<?php

namespace App\Services;

class PromptFormatService
{
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

    public function createEditEventContext($prompt, $location, $itinerary, $event, $promptContext)
    {
        $context = $promptContext->context;

        $compressedEvents = $this->compressEvents($itinerary->events);

        $compressedEvent = $this->compressEvent($event);

        $revisedContext = [];

        foreach ($context as $step) {
            $step['content'] = str_replace('<<prompt>>', $prompt, $step['content']);
            $step['content'] = str_replace('<<location>>', $location, $step['content']);
            $step['content'] = str_replace('<<events>>', $compressedEvents, $step['content']);
            $step['content'] = str_replace('<<event>>', $compressedEvent, $step['content']);
            $revisedContext[] = $step;
        }

        return $revisedContext;
    }

    public function createAddEventContext($prompt, $location, $order, $itinerary, $promptContext)
    {
        $context = $promptContext->context;

        $compressedEvents = $this->compressEvents($itinerary->events);

        $revisedContext = [];

        $previous = $order - 1;
        $next = $order + 1;

        if ($previous < 0) {
            $previous = 0;
        }

        if ($next > count($itinerary->events)) {
            $next = 'the end';
        }

        foreach ($context as $step) {
            $step['content'] = str_replace('<<prompt>>', $prompt, $step['content']);
            $step['content'] = str_replace('<<location>>', $location, $step['content']);
            $step['content'] = str_replace('<<previous>>', $previous, $step['content']);
            $step['content'] = str_replace('<<next>>', $next, $step['content']);
            $step['content'] = str_replace('<<events>>', $compressedEvents, $step['content']);
            $revisedContext[] = $step;
        }

        return $revisedContext;
    }

    function extractEvents($response)
    {

        // Find all content within square brackets
        $pattern = '/\[([^]]+)\]/';
        preg_match_all($pattern, $response, $matches);

        // Initialize an empty events array
        $events = [];

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
            $title = $event->title;
            $location = $event->location->name;

            // Combine the extracted fields into a single line using '|' separator
            $compressedEvent = "[{$uuid}|{$title}|{$location}]";

            // Add the compressed event line to the compressed itinerary string, followed by a newline character
            $compressedItinerary .= $compressedEvent . "\n";
        }

        return $compressedItinerary;
    }

    public function compressEvent($event)
    {
        // Initialize an empty compressed itinerary string
        $compressedItinerary = '';

        $uuid = $event->uuid;
        $title = $event->title;
        $description = $event->description;
        $location = $event->location->name;

        $compressedItinerary = "[e|{$uuid}|{$title}|{$description}|{$location}]";

        foreach ($event->activities as $activity) {
            $title = $activity->title;
            $description = $activity->description;

            // Combine the extracted fields into a single line using '|' separator
            $compressedActivity = "[a|{$title}|{$description}]";

            // Add the compressed event line to the compressed itinerary string, followed by a newline character
            $compressedItinerary .= $compressedActivity . "\n";
        }

        return $compressedItinerary;
    }

    public function extractEventDetails($response)
    {

        // Remove any extra text before the compressed event
        $pattern = '/\[([^]]+)\]/';
        preg_match_all($pattern, $response, $matches);

        $event = [];
        $activities = [];

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
                    'title' => $data[1],
                    'description' => $data[2],
                ];
            }
        }

        return [
            'event' => $event,
            'activities' => $activities,
        ];
    }

    public function extractFullEvent($response)
    {
        // Remove any extra text before the compressed event
        $pattern = '/\[([^]]+)\]/';
        preg_match_all($pattern, $response, $matches);

        $event = [];
        $activities = [];

        foreach ($matches[1] as $line) {
            error_log($line);

            $data = explode('|', $line);
            $type = trim($data[0]);

            if ($type === 'e') {
                $event = [
                    'title' => $data[1],
                    'description' => $data[2],
                    'location' => $data[3],
                ];
            } elseif ($type === 'a') {
                $activities[] = [
                    'title' => $data[1],
                    'description' => $data[2],
                ];
            }
        }

        return [
            'event' => $event,
            'activities' => $activities,
        ];
    }
}
