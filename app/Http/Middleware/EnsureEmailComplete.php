<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (filled($user->email)) {
            return $next($request);
        }

        if ($request->routeIs('profile.complete-email', 'profile.complete-email.store', 'logout')) {
            return $next($request);
        }

        return redirect()->route('profile.complete-email');
    }
}
