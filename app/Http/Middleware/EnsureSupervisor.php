<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupervisor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect('/supervisorSignup');
        }

        $user = Auth::user();

        if (! $user->hasRole('supervisor')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/supervisorSignup')->withErrors([
                'email' => 'You do not have access to the supervisor portal.',
            ]);
        }

        $supervisor = $user->supervisor;

        if (! $supervisor) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/supervisorSignup')->withErrors([
                'email' => 'Supervisor profile not found. Contact the administrator.',
            ]);
        }

        Session::put('email', $user->email);
        Session::put('name', $user->name);
        // Temporary compatibility for existing supervisor queries; remove in session cleanup phase.
        Session::put('id', $supervisor->id);

        return $next($request);
    }
}
