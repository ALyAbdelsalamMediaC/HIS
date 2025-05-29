<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;

class UserAuthController extends Controller
{

    public function login(Request $request)
{
    $credentials = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    if (!Auth::attempt($credentials)) {
        Log::create([
            'user_id' => null,
            'type' => 'login_failed',
            'description' => 'Failed login attempt for username: ' . $credentials['username'],
        ]);
        return response()->json([
            'error' => 'Invalid credentials',
        ], 401);
    }

    $user = Auth::user();

    Log::create([
        'user_id' => $user->id,
        'type' => 'login_success',
        'description' => 'User logged in: ' . $user->username,
    ]);

    // If you use Laravel Sanctum or Passport, generate token here
    // $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'user' => $user,
        // 'token' => $token,
    ], 200);
}

    public function register(Request $request)
    {
        try {
            // Validate input first
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'role'     => 'required|string',
                'username' => 'required|string|max:255',
                'phone'    => 'required|string|max:20',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Check if device_id already exists
            $existingDevice = User::where('email', $validated['email'])
                ->first();

            if ($existingDevice) {
                // Log attempt
                Log::create([
                    'user_id' => Auth::id(),
                    'type' => 'user_already_exists',
                    'description' => 'Device ID or emailalready exists: ' . $validated['device_id'],
                ]);

                return response()->json([
                    'success' => 'Device ID or email already registered',
                    'message' => 'A user with this device ID or emailalready exists.',
                ], 200); // 409 Conflict
            }

            // Create user
            $user = User::create($validated);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'user_create_success',
                'description' => 'User created: ' . $user->email,
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            LaravelLog::error($e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'user_create_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to create user',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
