<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\UserRepository;
use App\Models\Trip;
use App\Models\Rating;

class UserController extends Controller
{
    protected $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function user()
    {
        $user = $this->userRepo->currentAuthenticatedUser();
        return response()->json([
            'success' => true,
            'message' => 'User recieved',
            'user' => $user
        ]);
    }

    public function registerUserWithPassword(Request $request)
    {  
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = $this->userRepo->createUser(
            $request['name'],
            $request['email'],
            $request['password']
        );

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'User successfully registered!'
        ], 201);
    }

    public function updateUser(Request $request)
    {
        $request->validate([
            'email' => 'email|unique:users',
            'password' => 'min:6',
        ]);
        $user = $this->userRepo->updateUser(
            $request->only('name','email'), 
            $request->has('password') ? $request['password'] : null);
        return response()->json([
            'success' => true,
            'message' => 'User successfully updated!',
            'user' => $user
        ], 200);
    }

    public function deleteSelf()
    {
        $this->userRepo->deletSelf();
        return response()->json([
            'success' => true,
            'message' => 'User successfully deleted!'
        ], 200);
    }

    public function getUserTrips()
    {
        $trips = $this->userRepo->getTrips();
        return response()->json([
            'success' => true,
            'message' => 'Trips recieved',
            'trips' => $trips
        ]);
    }

    public function addFavoriteTrip($id)
{
    $trip = Trip::find($id);
    $user = $this->userRepo->currentAuthenticatedUser();
    $user->favoriteTrips()->syncWithoutDetaching($trip);
    return response()->json([
        'success' => true,
        'message' => 'Trip added to favorites',
        'trip' => $trip
    ]);
}

public function removeFavoriteTrip($id)
{
    $trip = Trip::find($id);
    $user = $this->userRepo->currentAuthenticatedUser();
    $user->favoriteTrips()->detach($trip);
    return response()->json([
        'success' => true,
        'message' => 'Trip removed from favorites',
        'trip' => $trip
    ]);
}

public function storeRating(Request $request)
{
    $request->validate([
        'value' => 'required',
        'trip_id' => 'required|exists:trips,id'
    ]);

    $trip = Trip::find($request['trip_id']);
    $rating = new Rating;
    $rating->value = $request['value'];
    $rating->user()->associate($request->user());
    $trip->ratings()->save($rating);

    return response()->json([
        'success' => true,
        'message' => 'Rating successfully added',
        'rating' => $rating
    ], 201);
}

public function updateRating(Request $request)
{
    $request->validate([
        'value' => 'required',
        'trip_id' => 'required|exists:trips,id'
    ]);

    // get rating from trip id and user id
    $rating = $request->user()->ratings()->where('trip_id', $request['trip_id'])->first();

    $this->authorize('update', $rating);
    $trip = Trip::find($request['trip_id']);
    $rating = $trip->ratings()->where('user_id', $request->user()->id)->first();
    $rating->value = $request['value'];
    $rating->save();

    return response()->json([
        'success' => true,
        'message' => 'Rating successfully updated',
        'rating' => $rating
    ], 200);
}
}
