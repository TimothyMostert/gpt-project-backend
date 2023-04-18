<?php

namespace Database\Seeders;

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
        PromptContext::create([
            'name' => 'itinerary_creation_v01',
            'description' => 'This is the first version of the itinerary creation prompt context',
            'context' => [
                [
                    'role' => 'system',
                    'content' => "
                        You are TravelGPT, you transform travel ideas into custom itineraries formatted to structured json.
                    ",
                ],
                [
                    'role'  => 'user',
                    'content' => "
                        Given a prompt and a list of tags.
                        Generate a travel itinerary that matches the prompt and tags.
                        Follow the format of the json as a guide to the content required. 
                        The initial user input will be given in the following format:
                        Prompt: [prompt]
                        Tags: [tags]
                     ",
                ],
                [
                    'role' => 'assistant',
                    'content' => "
                        I understand! Please provide the json format.
                    ",
                ],
                [
                    'role' => 'user',
                    'content' => "
                        Use the following json format:
                        {
                            title: <DescriptiveTitle>,
                            events: [ // an ordered array of events on the itinerary (include at least 4) location events are separated by travel events
                                {
                                    type: <eventType>, // options: <<eventTypes>>
                                    // If type is 'travel', the following fields are required:
                                    mode: <travelMode>, // options: <<travelModes>>
                                    origin: <travelOrigin>, // 'Point of Departure' if not specified
                                    destination: <travelDestination>,
                                    // If type is 'location', the following fields are required:
                                    title: <eventTitle>, // descriptive title of the event
                                    location: <locationName>, // the name of the location
                                    description: <locationDescription>, // a brief description of the location
                                    activities: [ // an array of activities relevant to the location and itinerary requirements
                                        {
                                            title: <activityTitle>,
                                            description: <activityDescription>
                                            activityType: <activityType>, // options: <<activityTypes>>
                                        }
                                    ]
                                }
                            ]
                        }",
                ],
                [
                    'role' => 'assistant',
                    'content' => "
                            Understood, Please provide the prompt and tags.
                        ",
                ],
                [
                    'role' => 'user',
                    'content' => "
                            Prompt:
                            ###
                            <<prompt>>
                            ###
                            Tags:
                            ###
                            <<tags>>
                            ###
                        ",
                ]
            ]
        ]);

        PromptContext::create([
            'name' => 'itinerary_creation_v02',
            'description' => 'This is the second version of the itinerary creation prompt context',
            'context' => [
                [
                    'role' => 'system',
                    'content' => "
                    I am looking for help to create a travel itinerary using OpenAI natural language prompts and tags. The output should be a structured JSON with the following elements: 
                        {
                          title: string,
                          events: [
                            {
                              type: string, (options <<eventTypes>>)
                              mode: string, (if type is 'travel' options <<travelModes>>)
                              origin: string, (if type is 'travel')
                              destination: string, (if type is 'travel')
                              title: string, (if type is 'location')
                              location: string, (if type is 'location')
                              description: string, (if type is 'location')
                              activities: [
                                {
                                  title: string,
                                  description: string,
                                  activityType: string, (options <<activityTypes>>)
                              ] (if type is 'location')
                            }
                          ]
                        }
                    "
                ],
                [
                    'role'  => 'user',
                    'content' => "
                    In this task, you will receive a series of prompts and tags to create a travel itinerary. The input will consist of a '[prompt]' section that provides a main instruction and a '[tags]' section that gives more specific details or requirements for the output. For example:
                        [prompt]: Create a 3-day travel itinerary for a trip to San Francisco, California.
                        [tags]: San Francisco, California, 3 days, travel itinerary, JSON format
                     ",
                ],
                [
                    'role' => 'assistant',
                    'content' => "
                        I understand! Please provide the user input.
                    ",
                ],
                [
                    'role' => 'user',
                    'content' => "
                            ###
                            Prompt: <<prompt>>
                            Tags: <<tags>>
                            "
                ]
            ]
        ]);
    }
}
