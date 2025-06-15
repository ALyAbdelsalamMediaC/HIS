<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use App\Models\Log; // Assuming you have a Log model
use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Facades\Hash;

class SocialAuthController extends Controller
{
    /**
     * Generate a unique username based on name or email.
     *
     * @param string $name
     * @param string $email
     * @return string
     */
    protected function generateUniqueUsername($name, $email)
    {
        // Base username from name (remove spaces, lowercase)
        $baseUsername = Str::slug($name, '');
        if (empty($baseUsername)) {
            // Fallback to email prefix if name is empty
            $baseUsername = Str::before($email, '@');
        }

        $username = $baseUsername;
        $counter = 1;

        // Ensure uniqueness by appending a number if needed
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            // Check if user exists by google_id or email
            $user = User::where('google_id', $socialUser->id)
                ->orWhere('email', $socialUser->email)
                ->first();
            

            if ($user) {
                // Update google_id if not set
                if (!$user->google_id) {
                    $user->update(['google_id' => $socialUser->id]);
                }

                // Check if user has admin or reviewer role
                if (!in_array($user->role, ['admin', 'reviewer'])) {
                    Log::create([
                        'user_id' => $user->id,
                        'type' => 'login_error',
                        'description' => "Google login failed: user is not admin or reviewer (Email: {$socialUser->email})",
                    ]);
                    return redirect()->route('login')->withErrors(['login' => 'Access denied. Admins or reviewers only.']);
                }

                Auth::login($user);
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_success',
                    'description' => "Google login successful for {$socialUser->email}",
                ]);
            } else {
                // Create new user with a unique username
                $username = $this->generateUniqueUsername($socialUser->name, $socialUser->email);

                $user = User::create([
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'google_id' => $socialUser->id,
                    'username' => $username,
                    'role' => 'admin', // Adjust as needed
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                ]);

                Auth::login($user);
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'registration',
                    'description' => "New admin registered via Google ({$socialUser->email})",
                ]);
            }

            return redirect()->intended(route('pages.admin.dashboard'));
        } catch (\Exception $e) {
            Log::error("Google login error: " . $e->getMessage());
            return redirect()->route('login')->withErrors(['login' => 'Google authentication failed. Please try again.']);
        }
    }

    public function redirectToApple()
    {
        return Socialite::driver('apple')->redirect();
    }

    public function handleAppleCallback()
    {
        try {
            $socialUser = Socialite::driver('apple')->stateless()->user();

            // Check if user exists by apple_id or email
            $user = User::where('apple_id', $socialUser->id)
                ->orWhere('email', $socialUser->email)
                ->first();

            if ($user) {
                // Update apple_id if not set
                if (!$user->apple_id) {
                    $user->update(['apple_id' => $socialUser->id]);
                }

                // Check if user has admin or reviewer role
                if (!in_array($user->role, ['admin', 'reviewer'])) {
                    Log::create([
                        'user_id' => $user->id,
                        'type' => 'login_error',
                        'description' => "Apple login failed: user is not admin or reviewer (Email: {$socialUser->email})",
                    ]);
                    return redirect()->route('login')->withErrors(['login' => 'Access denied. Admins or reviewers only.']);
                }

                Auth::login($user);
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_success',
                    'description' => "Apple login successful for {$socialUser->email}",
                ]);
            } else {
                // Create new user with a unique username
                $username = $this->generateUniqueUsername($socialUser->name ?? 'Apple User', $socialUser->email);

                $user = User::create([
                    'name' => $socialUser->name ?? 'Apple User',
                    'email' => $socialUser->email,
                    'apple_id' => $socialUser->id,
                    'username' => $username,
                    'role' => 'admin', // Adjust as needed
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                ]);

                Auth::login($user);
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'registration',
                    'description' => "New admin registered via Apple ({$socialUser->email})",
                ]);
            }

            return redirect()->intended(route('pages.admin.dashboard'));
        } catch (\Exception $e) {
            Log::error("Apple login error: " . $e->getMessage());
            return redirect()->route('login')->withErrors(['login' => 'Apple authentication failed. Please try again.']);
        }
    }
    public function handleGoogleLoginApi(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string', // Google ID token from mobile app
        ]);

        try {
            // Verify the Google ID token
            $socialUser = Socialite::driver('google')->stateless()->userFromToken($request->id_token);
            // Check if user exists by google_id or email
            $user = User::where('google_id', $socialUser->id)
                ->orWhere('email', $socialUser->email)
                ->first();

           

            if ($user) {
                // Update google_id if not set
                if (!$user->google_id) {
                    $user->update(['google_id' => $socialUser->id]);
                }

                // Check if user has admin or reviewer role
                if (!in_array($user->role, ['admin', 'reviewer'])) {
                    Log::create([
                        'user_id' => $user->id,
                        'type' => 'login_error',
                        'description' => "Google API login failed: user is not admin or reviewer (Email: {$socialUser->email})",
                    ]);
                    return response()->json([
                        'error' => 'Access denied. Admins or reviewers only.',
                    ], 403);
                }

                // Log successful login
                Log::create([
                    'user_id' => $user->id,
                    'type' => 'login_success',
                    'description' => "Google API login successful for {$socialUser->email}",
                ]);
            } else {
                // Create new user with a unique username
                $username = $this->generateUniqueUsername($socialUser->name, $socialUser->email);

                $user = User::create([
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'google_id' => $socialUser->id,
                    'username' => $username,
                    'role' => 'user', // Adjust as needed
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                ]);

                Log::create([
                    'user_id' => $user->id,
                    'type' => 'registration',
                    'description' => "New admin registered via Google API ({$socialUser->email})",
                ]);
            }

            // Generate Sanctum token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            // Log::error("Google API login error: " . $e->getMessage());
            return response()->json([
                'error' => 'Google authentication failed. Please try again.',
            ], 401);
        }
    }
}