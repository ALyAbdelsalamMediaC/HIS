<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RedirectIfSessionExpired
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            Session::put('url.intended', $request->url());
            return redirect()->route('admin.login')->with('error', 'Session expired. Please log in again.');
        }

        return $next($request);
    }
}