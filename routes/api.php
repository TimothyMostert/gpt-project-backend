<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\GoogleAPIController;
use App\Http\Controllers\LoginController;

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

Route::get('user/logout', [LoginController::class, 'logoutUser']);
Route::get('user/auth', [LoginController::class, 'authenticateUserFromToken']);

Route::post('events/create', [ItineraryController::class, 'createEventsItinerary']);
Route::post('event/details', [ItineraryController::class, 'createEventDetails']);
Route::post('event/edit', [ItineraryController::class, 'editEvent']);
Route::post('event/add', [ItineraryController::class, 'addEvent']);
Route::get('prompt/create', [ItineraryController::class, 'createRandomPrompt']);

Route::post('google/places/photos', [GoogleAPIController::class, 'getPhotosFromLocation']);
