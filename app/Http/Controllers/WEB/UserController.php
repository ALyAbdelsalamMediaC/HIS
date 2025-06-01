<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::withTrashed()->get();
        return view('pages.users.index', compact('users'));
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

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            $user = Auth::user();

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
    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('pages.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'device_id' => 'nullable|string|max:255',
            'is_reviewer' => 'nullable|boolean',
            'role' => 'required|in:admin,reviewer,user',
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Soft delete the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return redirect()->route('users.index')->with('success', 'User restored successfully');
    }
}
