<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\UserRepository;

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

    public function addFavoriteTrip(Request $request, $id)
{
    $trip = Trip::find($id);
    $request->auth()->user()->favoriteTrips()->syncWithoutDetaching($trip);
    return response()->json([
        'success' => true,
        'message' => 'Trip added to favorites',
        'trip' => $trip
    ]);
}

public function removeFavoriteTrip(Request $request, $id)
{
    $trip = Trip::find($id);
    $request->auth()->user()->favoriteTrips()->detach($trip);
    return response()->json([
        'success' => true,
        'message' => 'Trip removed from favorites',
        'trip' => $trip
    ]);
}
