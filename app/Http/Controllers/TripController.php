<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Services\OpenaiAPIService;
use App\Services\GooglePlacesAPIService;
use App\Services\PromptFormatService;
use App\Services\UnsplashAPIService;

use App\Models\Trip;
use App\Models\Prompt;
use App\Models\PromptContext;
use App\Models\Location;
use App\Models\Activity;
use App\Models\TravelTag;
use App\Models\Event;

class TripController extends Controller
{
    private $openaiAPIService;
    private $promptFormatService;
    private $googleApiService;
    private $unsplashAPIService;

    public function __construct()
    {
        $this->openaiAPIService = new OpenaiAPIService();
        $this->promptFormatService = new PromptFormatService();
        $this->googleApiService = new GooglePlacesAPIService();
        $this->unsplashAPIService = new UnsplashAPIService();
    }

    public function createEventsTrip(Request $request)
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

        // create trip title
        $context = $this->promptFormatService->createTitleContext($request->prompt);
        $rawTitle = $this->openaiAPIService->contextualPrompt($context, 100, 'gpt-3.5-turbo', 0);
        $title = trim(preg_replace('/\s\s+/', ' ', $rawTitle->choices[0]->message->content));

        // create prompt context
        $context = $this->promptFormatService->createEventsContext($request->prompt, $request->interests, $promptContext);
        $prompt->prompt_with_context = $context;

        // create events
        $rawEvents = $this->openaiAPIService->contextualPrompt($context, 2048, $request['model'], 0);
        $events = $this->promptFormatService->extractEvents($rawEvents->choices[0]->message->content);

        // create prompt response and trip
        $prompt->promptResponses()->create([
            'prompt_id' => $prompt->id,
            'formatted' => $events ? true : false,
            'response' => $events ?? "Failed to create events. Please try again.",
            'raw_response' => $rawEvents->choices[0]->message->content
        ]);
        $trip = Trip::create([
            'user_id' => auth()->user()->id ?? 1,
            'prompt_id' => $prompt->id,
            'title' => trim($title, '"') ?? 'Untitled Trip',
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

            // If using Google Places API, get place details
            if (env('USE_GOOGLE_PLACES') && !$location->place_id) {
                $place = $this->googleApiService->findPlaceFromText($location->name, 'place_id,geometry');
                // if place details are not an error, save them to the location
                if (!isset($place['error'])) {
                    $location->place_id = $place['place_id'];
                    $location->latitude = $place['geometry']['location']['lat'] ?? "";
                    $location->longitude = $place['geometry']['location']['lng'] ?? "";
                    $location->save();
                }
            }

            // if no photo references, get a photo from unsplash
            if (env('USE_UNSPLASH') && !$location->photo_references) {
                $photos = $this->unsplashAPIService->searchPhotosByLocation($location->name);
                if (!isset($photos['error'])) {
                    $location->photo_references = $photos;
                    $location->save();
                }
            }

            $eventModel = Event::create([
                'trip_id' => $trip->id,
                'event_type' => 'location',
                'uuid' => $event['uuid'],
                'title' => $event['title'] ?? 'Untitled Event',
                'location_id' => $location->id,
                'order' => $key,
            ]);

            $eventModels[] = $eventModel;
        };

        // link events to trip
        $trip->events()->saveMany($eventModels);

        // get a photo from the events and set it as the trip photo
        // if neccessary keep searching events until a photo is found
        $main_photo = null;
        foreach ($eventModels as $event) {
            if ($event->location->photo_references) {
                $main_photo = $event->location->photo_references[0];
                break;
            }
        }

        if ($main_photo) {
            $trip->main_photo = $main_photo;
            $trip->save();
        } else {
            Log::info('No main photo found for trip: ' . $trip->id);
        }

        return response()->json([
            'trip' => $trip->load(['events', 'events.location',]),
            'success' => true
        ]);
    }

    public function createEventDetails(Request $request)
    {
        $request->validate([
            'uuid' => 'required',
            'trip_id' => 'required',
            'prompt_context' => 'required',
            'session_id' => 'required',
            'model' => 'required',
        ]);

        $promptContext = PromptContext::where('name', $request->prompt_context)->first();

        $trip = Trip::with(['events', 'events.location'])->find($request->trip_id);

        $context = $this->promptFormatService->createEventDetailsContext($request->uuid, $trip, $promptContext);

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

        $eventModel = $trip->events->where('uuid', $request->uuid)->first();
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
            'trip_id' => 'required',
            'prompt' => 'required',
            'prompt_context' => 'required',
            'session_id' => 'required',
            'model' => 'required',
        ]);

        // get trip
        $trip = Trip::with(['events', 'events.location', 'events.activities'])->find($request->trip_id);

        // get event
        $event = $trip->events->where('id', $request->event_id)->first();

        // get prompt context
        $promptContext = PromptContext::where('name', $request->prompt_context)->first();

        $location = $request->location ?? $event->location->name;

        // create prompt context
        $context = $this->promptFormatService->createEditEventContext($request->prompt, $location, $trip, $event, $promptContext);

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

        // create prompt response and trip
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
            'trip_id' => 'required',
            'prompt_context' => 'required',
            'prompt' => 'required',
            'location' => 'required',
            'order' => 'required',
            'session_id' => 'required',
            'model' => 'required',
        ]);

        // get trip
        $trip = Trip::with(['events', 'events.location', 'events.activities'])->find($request->trip_id);

        // get prompt context
        $promptContext = PromptContext::where('name', $request->prompt_context)->first();

        // create prompt context
        $context = $this->promptFormatService->createAddEventContext($request->prompt, $request->location, $request->order, $trip, $promptContext);

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

        // create prompt response and trip
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
            'trip_id' => $trip->id,
            'event_type' => 'location',
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

    public function deleteTrip($id)
    {
        $trip = Trip::find($id);
        $trip->delete();
        return response()->json([
            'success' => true
        ]);
    }

    public function getTrip($id)
    {
        $trip = Trip::with(['events', 'events.location', 'events.activities', 'user', 'favoritedByUsers', 'ratings'])->find($id);

        // check all the events location have photo_references and fetch them if not
        if (env('USE_UNSPLASH')) {
            foreach ($trip->events as $event) {
                if (!$event->location->photo_references) {
                    $photos = $this->unsplashAPIService->searchPhotosByLocation($event->location->name);
                    if (!isset($photos['error'])) {
                        $event->location->photo_references = $photos;
                        $event->location->save();
                    }
                }
            }
        }

        return response()->json([
            'trip' => $trip,
            'success' => true
        ]);
    }

    public function searchTrips(Request $request)
    {
        // possible search params
        $perPage = $request->perPage ?? 10;
        $search = $request->search ?? "";
        $order = $request->order ?? 'desc';
        $sort = $request->sort ?? 'created_at';
        $page = $request->page ?? 1;

        // get trips
        $trips = Trip::with(['events', 'events.location', 'events.activities', 'user'])
            ->where('title', 'LIKE', "%{$search}%")
            // ->orWhere('description', 'LIKE', "%{$search}%")
            ->orderBy($sort, $order)
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'trips' => $trips,
            'success' => true
        ]);
    }

    public function getMap($id)
    {
        $trip = Trip::with(['events', 'events.location', 'events.activities'])->find($id);
        $events = $trip->events;
        
        // Generate a marker for each event
        $markers = [];
        foreach ($events as $event) {
            $location = $event->location;
            // get the location coordinates
            if (!$location->latitude || !$location->longitude) {
                $place = $this->googleApiService->findPlaceFromText($location->name, 'place_id,geometry');
                // if place details are not an error, save them to the location
                error_log(json_encode($place[0]));
                if (!isset($place[0]['error'])) {
                    $location->place_id = $place[0]['place_id'];
                    $location->latitude = $place[0]['geometry']['location']['lat'] ?? "";
                    $location->longitude = $place[0]['geometry']['location']['lng'] ?? "";
                    $location->save();
                };
            }
            $marker = 'pin-l(' . $location->longitude . ',' . $location->latitude . ')';
            if (str_contains($marker, 'pin-l(,')) {
                continue;
            }
            $markers[] = 'pin-l(' . $location->longitude . ',' . $location->latitude . ')';
        }
        if (count($markers) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'No locations found for this trip.'
            ]);
        }

        $markersString = implode(',', $markers);
        
        // The Mapbox static map URL
        $mapUrl = 'https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/' . $markersString . '/auto/1200x1200?access_token=' . env('MAPBOX_ACCESS_TOKEN');

        return response()->json([
            'map' => $mapUrl,
            'success' => true
        ]);
    }
}
