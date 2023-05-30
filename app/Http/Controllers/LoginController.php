<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Repository\UserRepository;
use Laravel\Sanctum\PersonalAccessToken;


class LoginController extends Controller
{
    protected $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function authenticateUserFromToken(Request $request) {
        // check if the token is valid
        $token = $request->bearerToken();
        if (!$token) {
            return new JsonResponse([
                'error' => 'Token not found.'
            ], Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        return response()->json([
            'user' => $user
        ]);
    }

    public function loginWithPassword(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = $this->userRepo->getUserByEmail($request['email']);

        if (!$user) {
            return new JsonResponse([
                'error' => 'User does not exist.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('Access-Token')->plainTextToken;

        return response()->json([
                'user' => $user,
                'token' => $token
        ], 200, ['token' => $token])
            ->header('Access-Control-Expose-Headers', 'token');
    }

    public function logoutUser(Request $request) 
    {
    // Get user's current token
    $token = $request->user()->currentAccessToken();

    // Revoke the token
    $token->delete();

    return response()->json([
        'success' => true,
        'message' => 'User successfully logged out!'
    ], 200);
    }

    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }
        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    public function handleProviderCallback(Request $request, $provider)
    {
        $validated = $this->validateProvider($provider);

        if (!is_null($validated)) {
            return $validated;
        }

        try {
            $socialiteUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            error_log(json_encode($e->getMessage()));
            return response()->json(['error' => 'Invalid credentials provided', 'exception', $e->getMessage()], 422);
        }

        error_log(json_encode($socialiteUser));

        $user = $this->userRepo->createUserFromSocialite($socialiteUser, $provider);
        $token = $user->createToken('Access-Token')->plainTextToken;

        return response()->json([
                'success' => true,
                'user' => $user,
                'avatar' => $socialiteUser->getAvatar(),
            ], 200, ['token' => $token])
            ->header('Access-Control-Expose-Headers', 'token');
    }

    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'github', 'google'])) {
            return response()->json(['error' => 'Please login using facebook, github or google.'], 422);
        }
    }
}