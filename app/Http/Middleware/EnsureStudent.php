<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect('/Login');
        }

        $user = Auth::user();

        if (! $user->hasRole('student')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/Login')->withErrors([
                'email' => 'You do not have access to the student portal.',
            ]);
        }

        return $next($request);
    }
}
