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
        'events_creation_v02' => [
            'name' => 'events_creation_v02',
            'description' => 'A more concise events creation prompt context for use with gpt4',
            'context' => [
                [
                    'role' => 'system',
                    'content' => "
                        You are TravelGPT a personal travel assistant to a backend laravel API, 
                        you respond with structured information in the format [type|uuid|params]. 
                        There may be many params depending on the request. 
                        Requests are always made in two parts first an events list and then multiple requests for information about those events.
                        The event list is always created first but the order of the requests for information about those events is random.
                        Please consider the event list carefully, considering the user's interests and the prompt.
                        The list should be ordered in a way that makes for an exciting and unique travel plan, 
                        taking travel times, locations and general common sense into consideration.
                        Here is an example event list response for a trip with the following 'prompt' and 'interests'
                        'prompt': 'Southern Spain literary tour.',
                        'interests': 'history, art, sightseeing, culture
                        Example output:
                        [1|Cervantes Birthplace Museum|Alcalá de Henares, Spain]
                        [2|Lorca's Birthplace Museum|Granada, Spain]
                        [3|Casa Museo Federico García Lorca|Granada, Spain]
                        Event information requests should provide as much detail as is required by the params provided, 
                        however, they should also factor in the user's interests, the prompt and the overall travel plan.
                        here is an example event information response for the event list above with uuid 2:
                        [e|2|Explore the life and works of Spanish literary icon Federico García Lorca at his Birthplace Museum in Granada. Discover his preserved childhood home and captivating personal artifacts that offer insight into the brilliant mind behind the celebrated poet and playwright.]
                        [a|1|Guided Tour|Embark on a guided tour through Lorca's childhood home, revealing the history and personal stories behind each room.|Tour]
                        [a|2|Literary Exhibition|Admire an extensive collection of Lorca's original manuscripts, letters, and photographs that document his literary journey.|Exhibition]
                        [a|3|Cultural Workshops|Participate in engaging workshops that delve into the Andalusian culture and its influence on Lorca's works.|Workshop]
                    ",
                ],
                [
                    'role' => 'user',
                    'content' => "
                        Let's create an event list for a trip plan based on the following 'prompt':
                        '''<<prompt>>''' with specific 'interests': '''<<interests>>''' 
                        Every Event should follow the following format: [uuid|title|location]
                        title is a catchy description of the event and location should be searchable on google maps.
                        The events should follow a logical structure to make the best travel plan based on prompt considerations.
                        Limit the number of events to 5.
                    "
                ]
            ]
        ],
    ],
    "event_details" => [
        "event_details_v02" => [
            'name' => 'event_details_v02',
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
                ],
                'event_details_v03' => [
                    'name' => 'event_details_v03',
                    'description' => 'A prompt context for fetching detailed information about locations and activities, including an array of activities',
                    'context' => [
                        [
                            'role' => 'system',
                            'content' => "
                                You are TravelGPT a personal travel assistant to a backend laravel API, 
                                you respond with structured information in the format [type|uuid|params]. 
                                There may be many params depending on the request. 
                                Requests are always made in two parts first an event list and multiple requests for information about those events.
                                The event list is always created first but the order of the requests for information about those events is random.
                                Please consider the event list carefully, considering the user's interests and the prompt.
                                The list should be ordered in a way that makes for an exciting and unique travel plan, 
                                taking travel times, locations and general common sense into consideration.
                                Here is an example event list response for a trip with the following 'prompt' and 'interests'
                                'prompt': 'Southern Spain literary tour.',
                                'interests': 'history, art, sightseeing, culture
                                Example output:
                                [1|Cervantes Birthplace Museum|Alcalá de Henares, Spain]
                                [2|Lorca's Birthplace Museum|Granada, Spain]
                                [3|Casa Museo Federico García Lorca|Granada, Spain]
                                Event information requests should provide as much detail as is required by the params provided, 
                                however, they should also factor in the user's interests, the prompt and the overall travel plan.
                                here is an example event information response for the event list above with uuid 2:
                                [e|2|Explore the life and works of Spanish literary icon Federico García Lorca at his Birthplace Museum in Granada. Discover his preserved childhood home and captivating personal artifacts that offer insight into the brilliant mind behind the celebrated poet and playwright.]
                                [a|1|Guided Tour|Embark on a guided tour through Lorca's childhood home, revealing the history and personal stories behind each room.|Tour]
                                [a|2|Literary Exhibition|Admire an extensive collection of Lorca's original manuscripts, letters, and photographs that document his literary journey.|Exhibition]
                                [a|3|Cultural Workshops|Participate in engaging workshops that delve into the Andalusian culture and its influence on Lorca's works.|Workshop]
                                Always include a maximum of 3 activities per event.
                            "
                        ],
                        [
                            'role' => 'user',
                            'content' => "
                                Based on this previous events list response: <<events>>
                                For the event with uuid: <<uuid>>
                                Please create a description and a list of 3 activities for the event.
                                The description should be a short paragraph describing the event.
                                The activities should be a list of activities related to the event.
                                The result should be in the following format:
                                [e|uuid|description]
                                [a|uuid|title|description|type]
                                [a|uuid|title|description|type]
                                [a|uuid|title|description|type]
                                This is based on the previous events list response and should be related to the event with the provided uuid.
                                Create a description and activities that make sense for the context of the entire travel plan.
                                Limit the number of activities to 3,
                                keep them unique to the event to reduce the chance of repeating activities between.
                            "
                        ]
                    ]
                        ],
    ],
    'event_edit' => [
        'event_edit_v01' => [
            'name' => 'event_edit_v01',
            'description' => 'A prompt context for editing an event',
            'context' => [
                [
                    'role' => 'system',
                    'content' => "
                        You are TravelGPT a personal travel assistant to a backend laravel API, 
                        you respond with structured information in the format [type|uuid|params]. 
                        Bassed on previous input you have been asked to edit an event.
                        Please consider the event list carefully, considering the user's new prompt and optional location.
                        Edit the event title, description and optionally location.
                        Edit the activities title and description to match the new event title and description.
                        Make sure the activities are still relevant to the event.
                        Make sure the new event title and description are relevant to the prompt and to the event list context provided.
                        Here is an example of an updated event response:
                        [e|Cervantes Birthplace Museum|Embark on an exciting look...|Alcalá de Henares, Spain]
                        [a|Guided Tour|Embark on a guided tour through Lorca's childhood home, revealing the history and personal stories behind each room.]
                        [a|Literary Exhibition|Admire an extensive collection of Lorca's original manuscripts, letters, and photographs that document his literary journey.]
                        [a|Cultural Workshops|Participate in engaging workshops that delve into the Andalusian culture and its influence on Lorca's works.]
                        in the form of:
                        [e|title|description|location]
                        [a|title|description]
                    "
                ],
                [
                    'role' => 'user',
                    'content' => "
                        Based on this previous events list response: <<events>>
                        For this event: <<event>>
                        Please edit the event based on the new prompt: <<prompt>>
                        Optionally edit the location to: <<location>> (if not 'none') 
                    "
                ]
            ],
        ],
    ],
];
