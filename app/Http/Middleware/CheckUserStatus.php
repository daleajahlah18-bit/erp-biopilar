<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Auto-assign Administrator role if no roles exist
            if ($user->roles()->count() === 0) {
                $user->assignRole('Administrator');
            }

            if ($user->status !== 'Active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Your account is disabled or suspended. Please contact the administrator.');
            }
        }

        return $next($request);
    }
}
