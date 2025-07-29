<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Messaging::class, function () {
            $credentials = config('services.firebase.credentials');
            Log::debug('Attempting to initialize Firebase Messaging', ['credentials' => $credentials]);

            if (!$credentials || !file_exists($credentials)) {
                Log::error('Firebase credentials file not found or not set', ['path' => $credentials]);
                return null; // Return null if credentials are invalid
            }

            try {
                $factory = (new Factory)->withServiceAccount($credentials);
                $messaging = $factory->createMessaging();
                Log::info('Firebase Messaging initialized successfully');
                return $messaging;
            } catch (\Exception $e) {
                Log::error('Failed to initialize Firebase Messaging', ['error' => $e->getMessage()]);
                return null; // Return null on failure
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}