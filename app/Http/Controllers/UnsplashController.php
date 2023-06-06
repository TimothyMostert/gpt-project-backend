<?php

namespace App\Http\Controllers;

use App\Services\UnsplashAPIService;
use Illuminate\Http\Request;

class UnsplashController extends Controller
{
    public function photosFromLocation(Request $request, UnsplashAPIService $unsplashService)
{
    $photos = $unsplashService->searchPhotosByLocation($request['location']);
    
    return response()->json([
        'success' => true,
        'photos' => $photos
    ]);
}
}
