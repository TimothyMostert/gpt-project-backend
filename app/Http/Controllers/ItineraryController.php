<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\OpenaiAPIService;
use App\Services\PromptFormatService;

use App\Models\Itinerary;
use App\Models\Prompt;
use App\Models\PromptContext;
use App\Models\LocationEvent;
use App\Models\Location;
use App\Models\Activity;
use App\Models\TravelTag;
use App\Models\Event;

class ItineraryController extends Controller
{
    private $openaiAPIService;
    private $promptFormatService;

    public function __construct()
    {
        $this->openaiAPIService = new OpenaiAPIService();
        $this->promptFormatService = new PromptFormatService();
    }

    public function createEventsItinerary(Request $request)
    {
        $request->validate([
            'prompt' => 'required',
            'interests' => 'required',
            'prompt_context' => 'required',
            'session_id' => 'required',
        ]);

        $promptContext = PromptContext::where('name', $request->prompt_context)->first();

        $prompt = Prompt::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_context_id' => $promptContext->id,
            'prompt' => $request->prompt,
            'prompt_type' => 'events',
            'flagged' => false,
        ]);

        // create and link travel tags to prompt
        foreach ($request->interests as $interest) {
            $interest = TravelTag::firstOrCreate(['name' => $interest]);
            $prompt->travelTags()->attach($interest);
        }

        $flagged = $this->openaiAPIService->moderateInput($request->prompt);

        if ($flagged) {
            $prompt->flagged = true;
            $prompt->save();
            return response()->json([
                'success' => false,
                'message' => 'Your prompt contains inappropriate content.'
            ]);
        }

        $context = $this->promptFormatService->createEventsContext($request->prompt, $request->interests, $promptContext);

        $rawEvents = $this->openaiAPIService->contextualPrompt($context);

        $events = $this->promptFormatService->extractEvents($rawEvents->choices[0]->message->content);

        $prompt->promptResponses()->create([
            'prompt_id' => $prompt->id,
            'response_type' => $events ? 'formatted' : 'raw',
            'response' => $events ?? $rawEvents,
        ]);

        $itinerary = Itinerary::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_id' => $prompt->id,
            'title' => $events[0]->title ?? 'Untitled Itinerary',
        ]);

        if (!$events) {
            return response()->json([
                'success' => false,
                'message' => 'There was an error creating your events. Please try again.'
            ]);
        }

        $eventModels = [];

        foreach ($events as $key => $event) {

            $eventModel = Event::create([
                'itinerary_id' => $itinerary->id,
                'event_type_id' => 2,
                'uuid' => $event['uuid'],
                'order' => $key,
            ]);

            $location = Location::firstOrCreate([
                'name' => $event['location'] ?? 'Location not specified',
            ]);

            LocationEvent::create([
                'event_id' => $eventModel->id,
                'title' => $event['title'] ?? 'Untitled Event',

                'location_id' => $location->id,
            ]);

            $eventModels[] = $eventModel;
        };

        $itinerary->events()->saveMany($eventModels);

        return response()->json([
            'itinerary' => $itinerary->load(['events', 'events.locationEvent.location']),
            'success' => true
        ]);
    }

    public function createLocationDetails(Request $request)
    {
        $request->validate([
            'uuid' => 'required',
            'itinerary_id' => 'required',
            'prompt_context' => 'required',
            'session_id' => 'required',
        ]);


        $promptContext = PromptContext::where('name', $request->prompt_context)->first();

        $itinerary = Itinerary::with(['events', 'events.locationEvent.location'])->find($request->itinerary_id);

        $context = $this->promptFormatService->createEventDetailsContext($request->uuid, $itinerary, $promptContext);

        $prompt = Prompt::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_context_id' => $promptContext->id,
            'prompt' => "$request->uuid",
            'prompt_type' => 'location_details',
            'flagged' => false,
        ]);

        $rawLocationEvent = $this->openaiAPIService->contextualPrompt($context);

        $locationEventDetails = $this->promptFormatService->extractLocationEvent($rawLocationEvent->choices[0]->message->content);

        $prompt->promptResponses()->create([
            'prompt_id' => $prompt->id,
            'response_type' => $locationEventDetails ? 'formatted' : 'raw',
            'response' => $locationEventDetails ?? $rawLocationEvent,
        ]);

        if (!$locationEventDetails) {
            return response()->json([
                'success' => false,
                'message' => 'There was an error creating your location details. Please try again.'
            ]);
        }

        $eventModel = $itinerary->events->where('uuid', $request->uuid)->first();
        $locationEventModel = $eventModel->locationEvent;
        $locationEventModel->description = $locationEventDetails['event']['description'];
        $locationEventModel->save(); 

        foreach ($locationEventDetails['activities'] as $activity) {
            $activityModel = Activity::create([
                'location_event_id' => $locationEventModel->id,
                'title' => $activity['title'] ?? 'Untitled Activity',
                'description' => $activity['description'] ?? 'No description provided',
                'category' => $activity['category'] ?? 'Other',
                'uuid' => $activity['uuid'],
            ]);
            $locationEventModel->activities()->save($activityModel);
        }

        return response()->json([
            'locationDetails' => $locationEventModel->load(['location', 'activities']),
            'success' => true
        ]);
    }

    public function createRandomPrompt()
    {
        $prompts = config('prompts.random_itinerary_concepts');

        $randomPrompt = $prompts[array_rand($prompts)];

        return response()->json([
            'prompt' => $randomPrompt['prompt'],
            'tags' => $randomPrompt['tags'],
            'success' => true
        ]);
    }
}
