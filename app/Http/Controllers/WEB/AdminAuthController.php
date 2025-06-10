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
            'login' => 'required|string', // can be email or phone
            'password' => [
                'required',
            ],
        ]);

        try {
            // Determine if login is email or phone
            $loginInput = $credentials['login'];
            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $loginInput)->first();
                $loginField = 'email';
            } else {
                $user = User::where('phone', $loginInput)->first();
                $loginField = 'phone';
            }

            if (!$user) {
                Log::create([
                    'user_id' => null,
                    'type' => 'login_error',
                    'description' => "Login failed: no user with {$loginField} {$loginInput}",
                ]);
                return back()->withErrors(['login' => ucfirst($loginField) . ' does not exist.'])->onlyInput('login');
            }

            if (!($user->role === 'admin' || $user->role === 'reviewer')) {
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_error',
                    'description' => "Login failed: user is not admin (Login: {$loginInput})",
                ]);
                return back()->withErrors(['login' => 'Access denied. Admins only.'])->onlyInput('login');
            }

            // Attempt login
            if (Auth::attempt([$loginField => $loginInput, 'password' => $credentials['password']])) {
                $request->session()->regenerate();

                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_success',
                    'description' => "Admin login successful for {$loginInput}",
                ]);

                return redirect()->intended(route('pages.admin.dashboard'));
            } else {
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_error',
                    'description' => "Login failed: wrong password for {$loginInput}",
                ]);

                return back()->withErrors(['login' => 'Wrong password.'])->onlyInput('login');
            }
        } catch (\Exception $e) {
            LaravelLog::error("Login error: " . $e->getMessage());

            return back()->withErrors(['login' => 'Something went wrong. Please try again later.'])->onlyInput('login');
        }
    }

    public function showRegistrationForm()
    {
        return view('pages.users.add');
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