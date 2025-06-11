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
