<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Services\GooglePlacesAPIService;

class GoogleAPIController extends Controller
{
    private $googleApiService;

    public function __construct()
    {
        $this->googleApiService = new GooglePlacesAPIService();
    }

    public function getPhotosFromLocation(Request $request)
    {
        $request->validate([
            'location' => 'required|string'
        ]);

        $locationString = $request->input('location');

        $location = Location::where('name', $locationString)->first();

        $wasUpdated = false;

        // if the location doesnt have a photo reference, get one from google places api
        if (!$location || !$location->photo_references) {
            $placeDetails = $this->googleApiService->detailsFromLocation($locationString);
            // if not error
            if (!isset($placeDetails['error'])) {
                if ($location) {
                    $location->photo_references = $placeDetails['photoReferences'];
                    $location->latitude = $placeDetails['geometry']['lat'] ?? "";
                    $location->longitude = $placeDetails['geometry']['lng'] ?? "";
                    $location->save();
                } else {
                    $location = Location::create([
                        'name' => $locationString,
                        'latitude' => $placeDetails['geometry']['lat'] ?? "",
                        'longitude' => $placeDetails['geometry']['lng'] ?? "",
                        'photo_references' =>  $placeDetails['photoReferences']
                    ]);
                }
                $wasUpdated = true;
            }
        }

        return response()->json([
            'location' => $location,
            'wasUpdated' => $wasUpdated
        ]);
    }

    // 3 functions to test the new googleapiservice findPlaceFromText, getPlaceDetails, placeDetailsFromLocation
    public function findPlaceFromText(Request $request)
    {
        $request->validate([
            'location' => 'required',
        ]);

        $response = $this->googleApiService->findPlaceFromText($request['location']);

        if (isset($response['error'])) {
            return [
                'error' => $response['error'],
                'success' => false
            ];
        }

        return response()->json([
            'response' => $response,
            'success' => true
        ]);
    }

    public function getPlaceDetails(Request $request)
    {
        $request->validate([
            'place_id' => 'required',
        ]);

        $response = $this->googleApiService->getPlaceDetails($request['place_id']);

        if (isset($response['error'])) {
            return [
                'error' => $response['error'],
                'success' => false
            ];
        }

        return response()->json([
            'response' => $response,
            'success' => true
        ]);
    }

    public function detailsFromLocation(Request $request)
    {
        $request->validate([
            'location' => 'required',
        ]);

        $response = $this->googleApiService->detailsFromLocation($request['location']);

        if (isset($response['error'])) {
            return [
                'error' => $response['error'],
                'success' => false
            ];
        }

        return response()->json([
            'response' => $response,
            'success' => true
        ]);
    }
}
