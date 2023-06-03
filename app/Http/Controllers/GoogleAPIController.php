<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Services\GooglePlacesAPIService;

class GoogleAPIController extends Controller
{
    private $places;

    public function __construct()
    {
        $this->places = new GooglePlacesAPIService();
    }

    public function getPhotosFromLocation(Request $request)
    {
        $request->validate([
            'location' => 'required|string'
        ]);

        $locationString = $request->input('location');

        $photoReferences = $this->places->photoReferences($locationString);

        $location = Location::where('name', $locationString)->first();

        if ($location) {
            $location->photo_references = $photoReferences;
            $location->save();
        } else {
            $location = Location::create([
                'name' => $locationString,
                'photo_references' => $photoReferences
            ]);
        }

        return response()->json([
            'location' => $location
        ]);
    }


}
