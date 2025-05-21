<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;

class GuestController extends Controller
{
    public function store(Request $request)
{
    try {
        // Validate input first
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:guests,email',
            'phone' => 'required|string|max:20',
            'device_id' => 'required|string|max:20',
        ]);

        // Check if device_id already exists
        $existingDevice = Guest::where('device_id', $validated['device_id'])->first();

        if ($existingDevice) {
            // Log attempt
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'guest_create_error',
                'description' => 'Device ID already exists: ' . $validated['device_id'],
            ]);

            return response()->json([
                'error' => 'Device ID already registered',
                'message' => 'A guest with this device ID already exists.',
            ], 409); // 409 Conflict
        }

        // Create guest
        $guest = Guest::create($validated);

        // Log success
        Log::create([
            'user_id' => Auth::id(),
            'type' => 'guest_create_success',
            'description' => 'Guest created: ' . $guest->email,
        ]);

        return response()->json([
            'message' => 'Guest created successfully',
            'guest' => $guest,
        ], 201);

    } catch (\Exception $e) {
        LaravelLog::error($e->getMessage());

        Log::create([
            'user_id' => Auth::id(),
            'type' => 'guest_create_error',
            'description' => $e->getMessage(),
        ]);

        return response()->json([
            'error' => 'Failed to create guest',
            'message' => $e->getMessage(),
        ], 500);
    }
}

}
