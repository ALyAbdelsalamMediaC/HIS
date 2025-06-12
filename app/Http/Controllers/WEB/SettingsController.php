<?php

namespace App\Http\Controllers\WEB;
use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('pages.settings.index');
    }
    public function profile()
    {
        try {
            $user = Auth::user();
            $content = Media::where('user_id', $user->id)->get();
            return view('pages.users.profile', compact('user', 'content'));
        } catch (\Exception $e) {
            // Assuming you have a Log model and logs table
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'recently_added_error',
                'description' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while loading the profile.');
        }
    }
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            ]);

            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->username = $request->input('username');

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $path = $file->store('profile_pictures', 'public');
                $user->profile_picture = $path;
            }

            $user->save();

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'update_profile_error',
                'description' => $e->getMessage(),
            ]);
            return back()->with('error', 'An error occurred while updating the profile.');
        }
    }
    public function changePassword(Request $request)
    {
        try {
            $user = Auth::user();

            // Prevent password change if user registered via Google or Apple
            if (!empty($user->google_id) || !empty($user->apple_id)) {
                return back()->with('error', 'You cannot change the password for accounts registered with Google or Apple.');
            }

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('error', 'Current password is incorrect.');
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return back()->with('success', 'Password changed successfully.');
        } catch (\Exception $e) {
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'change_password_error',
                'description' => $e->getMessage(),
            ]);
            return back()->with('error', 'An error occurred while changing the password.');
        }
    }
}
