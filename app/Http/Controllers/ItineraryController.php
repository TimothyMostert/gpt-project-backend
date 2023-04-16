<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\OpenaiAPIService;
use App\Services\PromptFormatService;

use App\Models\Itinerary;
use App\Models\Prompt;
use App\Models\PromptContext;
use App\Models\EventType;
use App\Models\LocationEvent;
use App\Models\Location;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\TravelMode;
use App\Models\TravelEvent;
use App\Models\TravelTag;

class ItineraryController extends Controller
{
    private $openaiAPIService;
    private $promptFormatService;

    public function __construct()
    {
        $this->openaiAPIService = new OpenaiAPIService();
        $this->promptFormatService = new PromptFormatService();
    }

    public function generateBasicItinerary(Request $request)
    {
        $request->validate([
            'prompt' => 'required',
            'tags' => 'required',
            'prompt_context' => 'required',
            'session_id' => 'required',
        ]);

        $promptContextId = PromptContext::where('name', $request->prompt_context)->first()->id;

        $prompt = Prompt::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_context_id' => $promptContextId,
            'prompt' => $request->prompt,
            'prompt_type' => 'create itinerary',
            'flagged' => false,
        ]);

        // create and link travel tags to prompt
        foreach ($request->tags as $tag) {
            $tag = TravelTag::firstOrCreate(['name' => $tag]);
            $prompt->travelTags()->attach($tag);
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

        $context = $this->promptFormatService->createItineraryContext($request->prompt, $request->tags, $promptContextId);

        $rawItinerary = $this->openaiAPIService->contextualPrompt($context);

        $formattedItinerary = $this->promptFormatService->extractJson($rawItinerary->choices[0]->message->content);

        $prompt->promptResponses()->create([
            'prompt_id' => $prompt->id,
            'response' => $formattedItinerary,
        ]);

        error_log(json_encode($formattedItinerary));

        $itinerary = Itinerary::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_id' => $prompt->id,
            'title' => $formattedItinerary->title,
        ]);

        $eventTypes = EventType::all();
        $activityTypes = ActivityType::all();
        $travelModes = TravelMode::all();

        // create events from formattedItinerary events array
        foreach ($formattedItinerary->events as $key => $event) {
            
            $itinerary->events()->create([
                'itinerary_id' => $itinerary->id,
                'event_type_id' => $eventTypes->where('name', $event->type)->first()->id,
                'order' => $key,
            ]);

            // create appropriate event type location or travel
            switch ($event->type) {
                case 'location':
                    $currentEvent = LocationEvent::create([
                        'event_id' => $itinerary->events->last()->id,
                        'title' => $event->title,
                        'description' => $event->description,
                    ]);
                    $location = Location::firstOrCreate([
                        'name' => $event->location,
                    ]);
                    $currentEvent->location()->associate($location);
                    // create and attach activities to event
                    foreach ($event->activities as $activity) {
                        $activityType = $activityTypes->where('name', $activity->activityType)->first();
                        $nextActivity = Activity::create([
                            'title' => $activity->title,
                            'description' => $activity->description,
                            'activity_type_id' => $activityType ? $activityType->id : 17,
                        ]);
                        $currentEvent->activities()->save($nextActivity);
                    }
                    break;
                case 'travel':
                    $currentEvent = TravelEvent::create([
                        'event_id' => $itinerary->events->last()->id,
                        'travel_mode_id' => $travelModes->where('name', $event->mode)->first()->id,
                    ]);
                    $originLocation = Location::firstOrCreate([
                        'name' => $event->origin,
                    ]);
                    $destinationLocation = Location::firstOrCreate([
                        'name' => $event->destination,
                    ]);
                    $currentEvent->origin()->associate($originLocation);
                    $currentEvent->destination()->associate($destinationLocation);
                    break;
            }
        }
        
        return response()->json([
            'itinerary' => $formattedItinerary,
            'success' => true
        ]);
    }
}
