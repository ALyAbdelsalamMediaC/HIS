<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\GoogleDriveServiceImageProfile;

class UserController extends Controller
{
    protected $client;
    protected $GoogleDriveServiceImageProfile;
    

    public function __construct(
        GoogleDriveServiceImageProfile $GoogleDriveServiceImageProfile,
    ) {
        $this->GoogleDriveServiceImageProfile = $GoogleDriveServiceImageProfile;
        $this->client = $this->GoogleDriveServiceImageProfile->getClient(); // Ensure this method exists in the service
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        try {
            $query = User::withoutTrashed();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('name', 'like', '%' . $search . '%');
            }

            $users = $query->paginate(20)->withQueryString();
            $total_users = $query->count();
            return view('pages.users.index', compact('users', 'total_users'));
        } catch (\Exception $e) {
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'user_index_error',
                'description' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while loading the users.');
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
            'device_id' => 'nullable|string|max:255',
            'role' => 'required|in:admin,reviewer,user',
            'phone' => 'required|string',
            'profile_image' => 'nullable|image|max:2048',
        ]);


        $profile_image = null;

        if ($request->hasFile('profile_image')) {
            $driveServiceThumbnail = new GoogleDriveServiceImageProfile();
            if ($request->file('profile_image')->isValid()) {
                $filename = time() . '_' . $request->file('profile_image')->getClientOriginalName();
                $url = $driveServiceThumbnail->uploadImageProfile($request->file('profile_image'), $filename);
                $validated['profile_image']= 'https://lh3.googleusercontent.com/d/' . $url . '=w1000?authuser=0';
                
            }
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Soft delete the specified user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
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

    public function blocked(Request $request)
    {
        try {
            $query = User::onlyTrashed();

            // Apply search filter if provided
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Retrieve soft-deleted users with pagination
            $users = $query->select('id', 'name', 'email', 'phone', 'deleted_at')
                ->paginate(20)->withQueryString();

            // Get total number of deleted users (ignoring search filter)
            $totalDeleted = User::onlyTrashed()->count();

            return view('pages.users.blocked_users', compact('users', 'totalDeleted'));
        } catch (\Exception $e) {
            Log::create([
                'user_id' => Auth::id(),
                'type' => 'user_blocked_error',
                'description' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while loading blocked users.');
        }
    }
}
