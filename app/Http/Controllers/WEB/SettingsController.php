<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Services\GoogleDriveServiceImageProfile;

class SettingsController extends Controller
{
    protected $client;
    protected $GoogleDriveServiceImageProfile;
    

    public function __construct(
        GoogleDriveServiceImageProfile $GoogleDriveServiceImageProfile,
    ) {
        $this->GoogleDriveServiceImageProfile = $GoogleDriveServiceImageProfile;
        $this->client = $this->GoogleDriveServiceImageProfile->getClient(); // Ensure this method exists in the service
    }

    public function index()
    {
        return view('pages.settings.index');
    }
    public function profile()
    {
        try {
            $user = Auth::user();
            $content = Media::where('user_id', $user->id)->get();
            return view('pages.settings.profile.index', compact('user', 'content'));
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
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'required|string',
                'profile_image' => 'nullable|image',
            ]);
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            
            // Handle profile image upload if present (Google Drive)
            if ($request->hasFile('profile_image')) {
                $driveServiceThumbnail = new GoogleDriveServiceImageProfile();
                if ($request->file('profile_image')->isValid()) {
                    $filename = time() . '_' . $request->file('profile_image')->getClientOriginalName();
                    $url = $driveServiceThumbnail->uploadImageProfile($request->file('profile_image'), $filename);
                    $user->profile_image = 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                }
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
    public function showChangePasswordForm()
    {
        try {
            $user = Auth::user();
            if (!empty($user->google_id) || !empty($user->apple_id)) {
                return back()->with('error', 'You cannot change the password for accounts registered with Google or Apple.');
            }
            return view('pages.settings.changePassword.index');
        } catch (\Exception $e) {
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'show_change_password_form_error',
                'description' => $e->getMessage(),
            ]);
            return back()->with('error', 'An error occurred while loading the change password form.');
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
