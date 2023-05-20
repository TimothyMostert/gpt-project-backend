<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Services\OpenaiAPIService;
use App\Services\PromptFormatService;

use App\Models\Itinerary;
use App\Models\Prompt;
use App\Models\PromptContext;
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
            'model' => 'required',
        ]);

        // create prompt
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

        // check for inappropriate content
        $flagged = $this->openaiAPIService->moderateInput($request->prompt);
        if ($flagged) {
            $prompt->flagged = true;
            $prompt->save();
            return response()->json([
                'success' => false,
                'message' => 'Your prompt contains inappropriate content.'
            ]);
        }

        // create itinerary title
        $formattedPrompt = "Create a short itinerary title from the following prompt: '" . $request->prompt . "' only a couple of words long.";
        $title = $this->openaiAPIService->basicPrompt($formattedPrompt, 20, 'text-babbage-001')['choices'][0]['text'];

        // create prompt context
        $context = $this->promptFormatService->createEventsContext($request->prompt, $request->interests, $promptContext);
        $prompt->prompt_with_context = $context;

        // create events
        $rawEvents = $this->openaiAPIService->contextualPrompt($context, 2048, $request['model'], 0);
        $events = $this->promptFormatService->extractEvents($rawEvents->choices[0]->message->content);

        // create prompt response and itinerary
        $prompt->promptResponses()->create([
            'prompt_id' => $prompt->id,
            'formatted' => $events ? true : false,
            'response' => $events ?? "Failed to create events. Please try again.",
            'raw_response' => $rawEvents->choices[0]->message->content
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

        // create events
        $eventModels = [];
        foreach ($events as $key => $event) {
            $location = Location::firstOrCreate([
                'name' => $event['location'] ?? 'Location not specified',
            ]);
            $eventModel = Event::create([
                'itinerary_id' => $itinerary->id,
                'event_type_id' => 2,
                'uuid' => $event['uuid'],
                'title' => $event['title'] ?? 'Untitled Event',
                'location_id' => $location->id,
                'order' => $key,
            ]);
            $eventModels[] = $eventModel;
        };

        // link events to itinerary
        $itinerary->events()->saveMany($eventModels);

        return response()->json([
            'title' => trim(preg_replace('/\s\s+/', ' ', $title)),
            'itinerary' => $itinerary->load(['events', 'events.location']),
            'success' => true
        ]);
    }

    public function createEventDetails(Request $request)
    {
        $request->validate([
            'uuid' => 'required',
            'itinerary_id' => 'required',
            'prompt_context' => 'required',
            'session_id' => 'required',
            'model' => 'required',
        ]);

        $promptContext = PromptContext::where('name', $request->prompt_context)->first();

        $itinerary = Itinerary::with(['events', 'events.location'])->find($request->itinerary_id);

        $context = $this->promptFormatService->createEventDetailsContext($request->uuid, $itinerary, $promptContext);

        $prompt = Prompt::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_context_id' => $promptContext->id,
            'prompt' => "$request->uuid",
            'prompt_with_context' => json_encode($context),
            'prompt_type' => 'details',
            'flagged' => false,
        ]);

        $rawEventDetails = $this->openaiAPIService->contextualPrompt($context, 2048, $request['model'], 0);

        $eventDetails = $this->promptFormatService->extractEventDetails($rawEventDetails->choices[0]->message->content);

        $prompt->promptResponses()->create([
            'prompt_id' => $prompt->id,
            'formatted' => $eventDetails ? true : false,
            'response' => $eventDetails ?? "Failed to create event details. Please try again.",
            'raw_response' => $rawEventDetails->choices[0]->message->content
        ]);

        if (!$eventDetails) {
            return response()->json([
                'success' => false,
                'message' => 'There was an error creating your event details. Please try again.'
            ]);
        }

        $eventModel = $itinerary->events->where('uuid', $request->uuid)->first();
        $eventModel->description = $eventDetails['event']['description'];
        $eventModel->save();

        foreach ($eventDetails['activities'] as $activity) {
            $activityModel = Activity::create([
                'event_id' => $eventModel->id,
                'title' => $activity['title'] ?? 'Untitled Activity',
                'description' => $activity['description'] ?? 'No description provided',
            ]);
            $eventModel->activities()->save($activityModel);
        }

        return response()->json([
            'eventDetails' => $eventModel->load(['location', 'activities']),
            'success' => true
        ]);
    }

    public function editEvent(Request $request)
    {
        // validate request
        $request->validate([
            'event_id' => 'required',
            'itinerary_id' => 'required',
            'prompt' => 'required',
            'prompt_context' => 'required',
            'session_id' => 'required',
            'model' => 'required',
        ]);

        // get itinerary
        $itinerary = Itinerary::with(['events', 'events.location', 'events.activities'])->find($request->itinerary_id);

        // get event
        $event = $itinerary->events->where('id', $request->event_id)->first();

        // get prompt context
        $promptContext = PromptContext::where('name', $request->prompt_context)->first();

        $location = $request->location ?? $event->location->name;

        // create prompt context
        $context = $this->promptFormatService->createEditEventContext($request->prompt, $location, $itinerary, $event, $promptContext);

        // create prompt
        $prompt = Prompt::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_context_id' => $promptContext->id,
            'prompt' => $request->prompt,
            'prompt_with_context' => json_encode($context),
            'prompt_type' => 'edit',
            'flagged' => false,
        ]);

        // create prompt response
        $rawEvent = $this->openaiAPIService->contextualPrompt($context, 2048, $request['model'], 0);

        // extract event
        $event = $this->promptFormatService->extractFullEvent($rawEvent->choices[0]->message->content);

        // create prompt response and itinerary
        $prompt->promptResponses()->create([
            'prompt_id' => $prompt->id,
            'formatted' => $event ? true : false,
            'response' => $event ?? "Failed to create event. Please try again.",
            'raw_response' => $rawEvent->choices[0]->message->content
        ]);

        // update event
        $eventModel = Event::where('id', $request->event_id)->first();
        $eventModel->title = $event['event']['title'];
        $eventModel->description = $event['event']['description'];

        // update location
        $location = Location::firstOrCreate([
            'name' => $event['event']['location'] ?? 'Location not specified',
        ]);
        $eventModel->location_id = $location->id;

        // update activities
        $eventModel->activities()->delete();
        foreach ($event['activities'] as $activity) {
            $activityModel = Activity::create([
                'event_id' => $eventModel->id,
                'title' => $activity['title'] ?? 'Untitled Activity',
                'description' => $activity['description'] ?? 'No description provided',
            ]);
            $eventModel->activities()->save($activityModel);
        }

        $eventModel->save();

        return response()->json([
            'event' => $eventModel->load(['location', 'activities']),
            'success' => true
        ]);
    }

    public function addEvent(Request $request)
    {
        // validate request
        $request->validate([
            'itinerary_id' => 'required',
            'prompt_context' => 'required',
            'prompt' => 'required',
            'location' => 'required',
            'order' => 'required',
            'session_id' => 'required',
            'model' => 'required',
        ]);

        // get itinerary
        $itinerary = Itinerary::with(['events', 'events.location', 'events.activities'])->find($request->itinerary_id);

        // get prompt context
        $promptContext = PromptContext::where('name', $request->prompt_context)->first();

        // create prompt context
        $context = $this->promptFormatService->createAddEventContext($request->prompt, $request->location, $request->order, $itinerary, $promptContext);

        // create prompt
        $prompt = Prompt::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_context_id' => $promptContext->id,
            'prompt' => "Add Event",
            'prompt_with_context' => json_encode($context),
            'prompt_type' => 'add',
            'flagged' => false,
        ]);

        // create prompt response
        $rawEvent = $this->openaiAPIService->contextualPrompt($context, 2048, $request['model'], 0);

        // extract event
        $event = $this->promptFormatService->extractFullEvent($rawEvent->choices[0]->message->content);

        // create prompt response and itinerary
        $prompt->promptResponses()->create([
            'prompt_id' => $prompt->id,
            'formatted' => $event ? true : false,
            'response' => $event ?? "Failed to add event. Please try again.",
            'raw_response' => $rawEvent->choices[0]->message->content
        ]);

        if (!$event) {
            return response()->json([
                'success' => false,
            'message' => 'Failed to add event. Please try again.'
            ]);
        }

        $location = Location::firstOrCreate([
            'name' => $event['event']['location'] ?? 'Location not specified',
        ]);

        // create event
        $eventModel = Event::create([
            'description' => $event['event']['description'],
            'itinerary_id' => $itinerary->id,
            'event_type_id' => 2,
            'uuid' => Str::uuid(),
            'title' => $event['event']['title'] ?? 'Untitled Event',
            'location_id' => $location->id,
            'order' => $request->order,
        ]);

        // update the order of subsequent events
        

        // create activities
        foreach ($event['activities'] as $activity) {
            $activityModel = Activity::create([
                'event_id' => $eventModel->id,
                'title' => $activity['title'] ?? 'Untitled Activity',
                'description' => $activity['description'] ?? 'No description provided',
            ]);
            $eventModel->activities()->save($activityModel);
        }

        $eventModel->save();

        return response()->json([
            'event' => $eventModel->load(['location', 'activities']),
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
