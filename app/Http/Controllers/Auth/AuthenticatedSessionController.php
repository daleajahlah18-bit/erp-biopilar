<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $landingPage = RouteServiceProvider::getLandingPage();
        
        $intendedUrl = session()->pull('url.intended', $landingPage);
        
        // If the user's intended URL was /dashboard but they don't have permission,
        // override it with their fallback URL
        if (trim(parse_url($intendedUrl, PHP_URL_PATH), '/') === 'dashboard' && !auth()->user()->can('dashboard.visible')) {
            $intendedUrl = url($landingPage);
        }

        return redirect()->to($intendedUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
