<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TripController;
use App\Http\Controllers\GoogleAPIController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;

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

Route::get('user/trips', [UserController::class, 'getUserTrips']);

Route::post('trip/create', [TripController::class, 'createEventsTrip']);

Route::post('event/details', [TripController::class, 'createEventDetails']);
Route::post('event/edit', [TripController::class, 'editEvent']);
Route::post('event/add', [TripController::class, 'addEvent']);

Route::get('prompt/create', [TripController::class, 'createRandomPrompt']);

Route::post('google/places/photos', [GoogleAPIController::class, 'getPhotosFromLocation']);
