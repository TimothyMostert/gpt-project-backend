<?php

return [
    "events_creation" => [
        "events_creation_v01" => [
            'name' => 'events_creation_v01',
            'description' => 'A concise itinerary creation prompt context with sample responses',
            'context' => [
                [
                    'role' => 'system',
                    'content' => "
                        Create a travel plan based on a user's preferences and interests. 
                        The user input will be provided in the following format:

                        'prompt': 'Southern Spain literary tour.',
                        'interests': 'history, art, sightseeing, culture.'

                        Please provide locations in the following format using square brackets [] and vertical bars | as separators:
                        Include only the compressed format of the locations in your response (no extra text).
                        Use only uuids starting with 'e'.

                        [UUID|title|location].

                        Example output:

                        ###

                        [e1|Cervantes Birthplace Museum|Alcalá de Henares, Spain]
                        [e2|Lorca's Birthplace Museum|Granada, Spain]
                        [e3|Casa Museo Federico García Lorca|Granada, Spain]

                        ###

                        As per the example include no other text in your response keep it trimmed with no preamble or context.
                        Remember to enclose each entry in square brackets and use vertical bars | to separate fields.
                        Ensure these locations align with the user's prompt and interests to make an exciting and unique travel plan.
                    ",
                ],
                [
                    'role' => 'user',
                    'content' => "
                        ###
                        Prompt: <<prompt>>
                        Interests: <<interests>>
                    "
                ]
            ]
        ],
    ],
    "location_details" => [
        "location_details_v02" => [
            'name' => 'location_details_v02',
            'description' => 'A prompt context for fetching detailed information about locations and activities, including an array of activities',
            'context' => [
                [
                    'role' => 'system',
                    'content' => "
                        I would like you to generate a short description of a tourist event and a list of activities for that event, based on the provided user input:

                        'events': <<events>>,
                        'uuid': <<uuid>>
                        
                        Considering the events list provided 'events', create detailed information for the tourist event with the provided 'uuid'. For example:
                        
                        'events': [e1|title|location]
                                  [e2|title|location]
                                  [e3|title|location]
                        'uuid': e2
                        Would require an event description and activities related to the event with uuid e2.
                        
                        Please provide the information in the following compressed format:
                        
                        [e|UUID|Event Description]
                        [a|UUID|Activity Title|Activity Description|Activity Type]
                        
                        Here's an example for reference:
                        
                        [e|e1|A steep-sided canyon carved by the Colorado River in Arizona, USA. It is a popular tourist destination known for its layered bands of red rock and stunning vistas.]
                        [a|a1|Hiking the South Rim Trail|Walk along the South Rim of the Grand Canyon for breathtaking views.|Outdoor]
                        [a|a2|Rafting on the Colorado River|Experience the excitement of rafting through the Grand Canyon on the Colorado River.|Adventure]
                        [a|a3|Helicopter Tour|Take a thrilling helicopter tour for a unique perspective of the Grand Canyon.|Scenic]
                        
                        Now, please await the list of tourist events in the compressed format, and create an event description and activities that align with the given event.
                    "
                ],
                [
                    'role' => 'user',
                    'content' => "
                                    ###
                                    'events': <<events>>
                                    'uuid': <<uuid>>
                            "
                ]
            ]
        ]
    ],
];
