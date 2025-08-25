<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Log;
use App\Models\User;
use App\Services\GoogleDriveServiceImageProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    protected $client;
    protected $GoogleDriveServiceImageProfile;

    public function __construct(
        GoogleDriveServiceImageProfile $GoogleDriveServiceImageProfile,
    ) {
        $this->GoogleDriveServiceImageProfile = $GoogleDriveServiceImageProfile;
        $this->client = $this->GoogleDriveServiceImageProfile->getClient(); // Ensure this method exists in the service
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => 'required|string', // can be phone or email
            'password' => 'required|string',
        ]);

        // Determine if login is email or phone
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // Build credentials array for Auth::attempt
        $authCredentials = [
            $loginField => $credentials['login'],
            'password'  => $credentials['password'],
        ];

        if (!Auth::attempt($authCredentials)) {
            Log::create([
                'user_id' => null,
                'type' => 'login_failed',
                'description' => 'Failed login attempt for ' . $loginField . ': ' . $credentials['login'],
            ]);
            return response()->json([
                'error' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        // Check if user has the 'user' role
        if (!$user->hasRole('user')) {
            Auth::logout(); // Log out the user if role is not 'user'
            Log::create([
                'user_id' => $user->id,
                'type' => 'login_failed',
                'description' => 'Unauthorized role for ' . ($user->email ?? $user->phone),
            ]);
            return response()->json([
                'error' => 'Unauthorized: Only users with "user" role can log in',
            ], 403);
        }

        Log::create([
            'user_id' => $user->id,
            'type' => 'login_success',
            'description' => 'User logged in: ' . ($user->email ?? $user->phone),
        ]);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
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
                'phone' => 'required|string|unique:users,phone',
                'profile_image' => 'nullable|image|max:2048',
                'password' => 'required|string|min:8|confirmed',
                'academic_title' => 'nullable|string|max:255',
                'job_description' => 'nullable|string|max:255',
                'year_of_graduation' => 'nullable|date',
                'country_of_practices' => 'nullable|string|max:255',
                'institution' => 'nullable|string|max:255',
                'department' => 'nullable|string|max:255',
                'country_of_graduation' => 'nullable|string|max:255',
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

            $profile_image = null;

            if ($request->hasFile('profile_image')) {
                $driveServiceThumbnail = new GoogleDriveServiceImageProfile();
                if ($request->file('profile_image')->isValid()) {
                    $filename = time() . '_' . $request->file('profile_image')->getClientOriginalName();
                    $url = $driveServiceThumbnail->uploadImageProfile($request->file('profile_image'), $filename);
                    $profile_image = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
            }
            $validated['profile_image'] = $profile_image;
            // Create user
            $user = User::create($validated);

            // Log success
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'user_create_success',
                'description' => 'User created: ' . $user->email,
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'token' => $token,

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

    public function updateProfileImage(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([

                'user_id' => 'required',
                'profile_image' => 'nullable|image|max:2048',
            ]);

            $userId = (int) $validated['user_id'];
            // Get the authenticated user
            $user = User::where('id', $userId)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated or Deleted.',
                ], 401);
            }
            $profile_image = $user->profile_image; // Retain existing image if upload fails

            // Handle profile image upload
            if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
                $driveServiceThumbnail = new GoogleDriveServiceImageProfile();
                $filename = time() . '_' . $request->file('profile_image')->getClientOriginalName();
                $url = $driveServiceThumbnail->uploadImageProfile($request->file('profile_image'), $filename);
                $profile_image = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
            }

            // Update user's profile image
            $user->update(['profile_image' => $profile_image]);

            // Log success
            Log::create([
                'user_id' => $validated['user_id'],
                'type' => 'profile_image_update_success',
                'description' => 'Profile image updated for user: ' . $user->email,
            ]);

            return response()->json([
                'message' => 'Profile image updated successfully',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            LaravelLog::error($e->getMessage());

            Log::create([
                'user_id' => Auth::id(),
                'type' => 'profile_image_update_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update profile image',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !($user->role === 'user')) {
            Log::create([
                'user_id' => $user ? $user->id : null,
                'type' => 'password_reset_error',
                'description' => "Password reset failed: no user with email {$request->email}",
            ]);

            return response()->json([
                'errors' => ['email' => 'We can\'t find an user with that email address.'],
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            Log::create([
                'user_id' => $user->id,
                'type' => 'password_reset_error',
                'description' => "Password reset failed: incorrect current password for {$user->email}",
            ]);

            return response()->json([
                'errors' => ['current_password' => 'The current password is incorrect.'],
            ], 422);
        }

        // Update password directly
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        Log::create([
            'user_id' => $user->id,
            'type' => 'password_reset_success',
            'description' => "Password reset successful for {$user->email}",
        ]);

        return response()->json([
            'message' => 'Password reset successfully.',
        ], 200);
    }
    public function editProfile(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required',
                'email' => 'required',
            ]);

            $user = User::where('email', $validated['email'])->where('id', $validated['user_id'])->first();
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'No authenticated user found',
                ], 401);
            }

            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
                'academic_title' => 'nullable|string|max:255',
                'job_description' => 'nullable|string|max:255',
                'year_of_graduation' => 'nullable|date',
                'country_of_practices' => 'nullable|string|max:255',
                'institution' => 'nullable|string|max:255',
                'department' => 'nullable|string|max:255',
                'country_of_graduation' => 'nullable|string|max:255',

            ]);

            // Update user profile
            $user->update($validated);

            Log::create([
                'user_id' => $user->id,
                'type' => 'profile_update_success',
                'description' => "Profile updated for {$user->email}",
            ]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => new UserResource($user),
            ], 200);
        } catch (\Exception $e) {
            Log::create([
                'user_id' => $user->id ?? null,
                'type' => 'profile_update_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update profile',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteAccount(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'user_id' => 'required',
                'email' => 'required',
            ]);

            $user = User::where('email', $validated['email'])->where('id', $validated['user_id'])->first();
            if (!$user) {
                return response()->json([
                    'error' => 'User not found',
                ], 404);
            }

            if ($user->id == $validated['user_id'] || $user->email == $validated['email']) {
                $user->delete();

                Log::create([
                    'user_id' => $validated['user_id'],
                    'type' => 'account_deletion_success',
                    'description' => "Account deleted for {$validated['email']}",
                ]);

                return response()->json([
                    'message' => 'Account deleted successfully',
                ], 200);
            }
        } catch (\Exception $e) {
            Log::create([
                'user_id' => $request->input('user_id'),
                'type' => 'account_deletion_error',
                'description' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to delete account',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function userInformation(Request $request)
    {
        
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated',
                ], 401);
            }
            $user_data = User::where('id',$user->id)->first();
            return response()->json([
                'message' => 'User information retrieved successfully',
                'user' => $user_data,
            ], 200);
        } catch (Exception $e) {
            LaravelLog::error($e->getMessage());
            Log::create([
                'user_id' => $user->id,
                'type' => 'user_information_error',
                'description' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Failed to retrieve user information',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
