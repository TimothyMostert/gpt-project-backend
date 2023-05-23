<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function currentAuthenticatedUser() {
        return auth()->user();
    }

    public function getUserById($id) {
        return User::find($id);
    }

    public function getUserByEmail($email) {
        return User::firstWhere('email', $email);
    }

    public function createUser($name, $email, $password) {
        $user = User::firstOrNew([
            'email' => $email
        ]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->save();
        return $user;
    }

    public function createUserFromSocialite($socialiteUser, $provider) {
        $user = User::firstOrCreate(
            [
                'email' => $socialiteUser->getEmail()
            ],
            [
                'email_verified_at' => now(),
                'name' => $socialiteUser->getName(),
                'status' => true
            ]
        );

        $user->providers()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $socialiteUser->getId(),
            ],
            [
                'avatar' => $socialiteUser->getAvatar()
            ]
        );
        return $user;
    }

    public function updateUser($params, $password = null) {
        $user = $this->currentAuthenticatedUser();
        if ($password) {
            $user->password = Hash::make($password);
            $user->save();
        }
        $user->update($params);
        return $user;
    }

    public function deleteSelf() {
        $user = $this->currentAuthenticatedUser();
        $user::delete();
    }
}