<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Traits\JsonResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthService
{
    use JsonResponseTrait;
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

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'User registered successfully', 201);

        } catch (Exception $e) {
            Log::error('Error in registration: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function login(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return $this->errorResponse('The provided credentials are incorrect.', 401);            }

            $token =  $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');

        } catch (Exception $e) {
            Log::error('Error in login: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function logout(User $user)
    {
        try {
            $user->tokens()->delete();
            return $this->successResponse(null, 'Logged out successfully');
        } catch (Exception $e) {
            Log::error('Error in logout: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
