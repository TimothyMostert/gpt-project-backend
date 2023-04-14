<?php

namespace App\Services;

class PromptFormatService
{
    public function createItineraryContext($text, $tags)
    {
        $messages = [];

        // Set the initial system message
        $messages[] = [
            'role' => 'system',
            'content' => 'You are TravelGPT, a useful and accurate travel itinerary generator.',
        ];

        // Define an initial context
        $messages[] = [
            'role'  => 'user',
            'content' => 'You will be given a prompt and a list of tags.
             You must generate a detailed travel itinerary that matches the prompt and tags.
             The initial user input will be given in the following format:
             Prompt: <prompt>
             Tags: [<tags>]',
        ];
        $messages[] = [
            'role' => 'assistant',
            'content' => 'I understand! I will generate a travel itinerary that matches the prompt and tags.
             Please tell me how to format the repsonse.',
        ];
        $messages[] = [
            'role' => 'user',
            'content' => 'You will format a travel itinerary in the following json format with no other text:
                {
                    "title": "<DescriptiveTitle>",
                    "events": [
                        {
                            "type": "<eventType>", // either "travel" or "location"

                            // If type is "travel", then the following fields are required:
                            "mode": "<travelMode>", // either "car", "train", "plane", "bus", "boat", "walk", "bike", or "other"
                            "origin": "<travelOrigin>", // the origin of the travel (do not assume the users point of departure)
                            "destination": "<travelDestination>", // the destination of the travel

                            // If type is "location", then the following fields are required:
                            "location": "<locationName>", // the name of the location
                            "day": "<dayNumber>", // the day number of the location
                            "description": "<locationDescription>", // a description of the location
                            "activities": [
                                "<activity1>",
                                "<activity2>",
                                "<activity3>",
                                "<activity4>",
                                "<activity5>"
                            ]
                        }
                    ]
                }',
        ];
        $messages[] = [
            'role' => 'assistant',
            'content' => 'I will format the response in the above format only and no other text. Please give me the prompt and tags.',
        ];

        // Add the prompt and tags
        $messages[] = [
            'role' => 'user',
            'content' => 'Prompt: ' . $text . 
            'Tags: [' . implode(", ",$tags) . ']',
        ];


        return $messages;
    }

    function extractJson($string) {
        // Find the starting and ending positions of the JSON object
        $json_start = strpos($string, '{');
        $json_end = strrpos($string, '}');
    
        // Extract the JSON object from the string
        $json_string = substr($string, $json_start, $json_end - $json_start + 1);

        preg_replace('/\r|\n/','\n',trim($json_string));
    
        // Return the extracted JSON
        return $json_string;
    }
}
