<?php

namespace App\Http\Controllers;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog; // for system logs if needed


class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.admin-login'); // Create this Blade view
    }

  public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    try {
        // First find user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            // Log failed attempt (no such user)
            Log::create([
                'user_id' => null,
                'type' => 'login_error',
                'description' => "Login failed: no user with email {$credentials['email']}",
            ]);
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        if ($user->role !== 'admin') {
            // Log failed attempt (not admin)
            Log::create([
                'user_id' => $user->id,
                'type' => 'login_error',
                'description' => "Login failed: user is not admin (email: {$user->email})",
            ]);
            return back()->withErrors(['email' => 'Access denied. Admins only.'])->onlyInput('email');
        }

        // Attempt login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Log success
            Log::create([
                'user_id' => $user->id,
                'type' => 'login_success',
                'description' => "Admin login successful for {$user->email}",
            ]);

            return redirect()->intended(route('admin.dashboard'));
        } else {
            // Log failed attempt (wrong password)
            Log::create([
                'user_id' => $user->id,
                'type' => 'login_error',
                'description' => "Login failed: wrong password for {$user->email}",
            ]);

            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }
    } catch (\Exception $e) {
        // Log exception error (optional)
        LaravelLog::error("Login error: " . $e->getMessage());

        return back()->withErrors(['email' => 'Something went wrong. Please try again later.'])->onlyInput('email');
    }
}
}
