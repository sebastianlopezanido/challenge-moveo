<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthService
{
    public function register(array $data)
    {
        try {
            $userRole = Role::where('name', 'user')->firstOrFail();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $userRole->id,
            ]);

            return $user->createToken('auth_token')->plainTextToken;

        } catch (Exception $e) {
            Log::error('Error in registration: ' . $e->getMessage());
            throw new Exception("Registration failed. Please try again later.");
        }
    }

    public function login(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return null;
            }

            return $user->createToken('auth_token')->plainTextToken;

        } catch (Exception $e) {
            Log::error('Error in login: ' . $e->getMessage());
            throw new Exception("Login failed. Please try again later.");
        }
    }

    public function logout(User $user)
    {
        try {
            $user->tokens()->delete();
        } catch (Exception $e) {
            Log::error('Error in logout: ' . $e->getMessage());
            throw new Exception("Logout failed. Please try again later.");
        }
    }
}
