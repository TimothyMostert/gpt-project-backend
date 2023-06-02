<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        // validate
        $request->validate([
            'location' => 'required|string'
        ]);

        $locationString = $request->input('location');

        $photoReferences = $this->places->photoReferences($locationString);

        return response()->json([
            'photoReferences' => $photoReferences
        ]);
    }


}