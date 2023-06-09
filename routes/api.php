<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TripController;
use App\Http\Controllers\GoogleAPIController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnsplashController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // Routes with sanctum guard
    Route::get('user/logout', [LoginController::class, 'logoutUser']);
    Route::get('user/auth', [LoginController::class, 'authenticateUserFromToken']);
    Route::get('user/trips', [UserController::class, 'getUserTrips']);
    Route::post('trip/create', [TripController::class, 'createEventsTrip']);
    Route::get('trip/delete/{id}', [TripController::class, 'deleteTrip']);
    Route::post('event/details', [TripController::class, 'createEventDetails']);
    Route::post('event/edit', [TripController::class, 'editEvent']);
    Route::post('event/add', [TripController::class, 'addEvent']);
    Route::get('prompt/create', [TripController::class, 'createRandomPrompt']);
    Route::get('/trip/favorite/{id}', [UserController::class, 'addFavoriteTrip']);
    Route::delete('/trip/unfavorite/{id}', [UserController::class, 'removeFavoriteTrip']);
    Route::post('/trip/rating', [UserController::class, 'storeRating']);
Route::patch('/trip/rating', [UserController::class, 'updateRating']);
});

// Routes without sanctum guard
Route::post('trip/search', [TripController::class, 'searchTrips']);
Route::get('trip/{id}', [TripController::class, 'getTrip']);

// Google routes
Route::post('google/places/find', [GoogleAPIController::class, 'findPlaceFromText']);
Route::post('google/places/details', [GoogleAPIController::class, 'getPlaceDetails']);
Route::post('google/places/detailsFromLocation', [GoogleAPIController::class, 'detailsFromLocation']);

// Unsplash routes
Route::post('unsplash/photosFromLocation', [UnsplashController::class, 'photosFromLocation']);

// map
Route::get('map/{id}', [TripController::class, 'getMap']);
