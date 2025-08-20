<?php

namespace App\Http\Controllers\Auth;

use App\Services\AppleClientSecret;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppleAuthController extends Controller
{
    public function redirectToApple()
    {
        // Set the client secret dynamically
        config(['services.apple.client_secret' => AppleClientSecret::generate()]);
        return Socialite::driver('apple')
            ->scopes(['name', 'email'])
            ->redirect();
    }

    public function handleAppleCallback(Request $request)
    {
        try {
            // Set the client secret dynamically
            config(['services.apple.client_secret' => AppleClientSecret::generate()]);

            $appleUser = Socialite::driver('apple')->user();

            // Find or create the user in your database
            $user = User::updateOrCreate(
                ['apple_id' => $appleUser->id],
                [
                    'name' => $appleUser->name ?? 'Unknown',
                    'email' => $appleUser->email,
                    'password' => bcrypt(uniqid()), // Generate a random password
                ]
            );

            // Log the user in
            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            \Log::error('Apple login error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['apple' => 'Unable to login with Apple.']);
        }
    }
}