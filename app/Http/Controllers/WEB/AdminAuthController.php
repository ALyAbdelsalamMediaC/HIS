<?php

namespace App\Http\Controllers\WEB;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog; // for system logs if needed
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('pages.auth.admin-login'); // Create this Blade view
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => [
                'required',
                // 'string',
                // 'min:6',
                // 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
        ]);

        try {
            // First find user by email
            $user = User::where('username', $credentials['username'])->first();

            if (!$user) {
                // Log failed attempt (no such user)
                Log::create([
                    'user_id' => null,
                    'type' => 'login_error',
                    'description' => "Login failed: no user with username {$credentials['username']}",
                ]);
                return back()->withErrors(['Username' => 'Username does not exist.'])->onlyInput('username');
            }

            if (!($user->role === 'admin' || $user->role === 'reviewer')) {
                // Log failed attempt (not admin)
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_error',
                    'description' => "Login failed: user is not admin (Username: {$user->username})",
                ]);
                return back()->withErrors(['username' => 'Access denied. Admins only.'])->onlyInput('username');
            }

            // Attempt login
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                // Log success
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_success',
                    'description' => "Admin login successful for {$user->username}",
                ]);

                return redirect()->intended(route('pages.admin.dashboard'));
            } else {
                // Log failed attempt (wrong password)
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_error',
                    'description' => "Login failed: wrong password for {$user->username}",
                ]);

                return back()->withErrors(['username' => 'Wrong password.'])->onlyInput('username');
            }
        } catch (\Exception $e) {
            // Log exception error (optional)
            LaravelLog::error("Login error: " . $e->getMessage());

            return back()->withErrors(['username' => 'Something went wrong. Please try again later.'])->onlyInput('username');
        }
    }

    public function showRegistrationForm()
    {
        return view('pages.auth.admin-register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
        ]);
        try {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            // Optionally log the event
            Log::create([
                'user_id' => $user->id,
                'type' => 'registration',
                'description' => "New admin registered ({$user->username})",
            ]);

            // Auto‐login and redirect
            Auth::login($user);
            return redirect()->route('pages.admin.dashboard');

        } catch (\Exception $e) {
            LaravelLog::error("Registration error: " . $e->getMessage());
            return back()
                ->withErrors(['email' => 'Unable to register. Please try again later.'])
                ->withInput($request->except('password'));
        }
    }

    public function showForgotPasswordForm()
    {
        return view('pages.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !($user->role === 'admin' || $user->role === 'reviewer')) {
            Log::create([
                'user_id' => $user ? $user->id : null,
                'type' => 'password_reset_error',
                'description' => "Password reset failed: no admin user with email {$request->email}",
            ]);
            return back()->withErrors(['email' => 'We can\'t find an admin user with that email address.']);
        }
        
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            Log::create([
                'user_id' => $user->id,
                'type' => 'password_reset_request',
                'description' => "Password reset link sent to {$user->email}",
            ]);
            return back()->with(['status' => __($status)]);
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm($token)
    {
        return view('pages.auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !($user->role === 'admin' || $user->role === 'reviewer')) {
            Log::create([
                'user_id' => $user ? $user->id : null,
                'type' => 'password_reset_error',
                'description' => "Password reset failed: no admin user with email {$request->email}",
            ]);
            return back()->withErrors(['email' => 'We can\'t find an admin user with that email address.']);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                Log::create([
                    'user_id' => $user->id,
                    'type' => 'password_reset_success',
                    'description' => "Password reset successful for {$user->email}",
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}