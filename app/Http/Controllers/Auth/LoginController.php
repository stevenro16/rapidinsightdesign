<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()->is_active) {
                Auth::logout();
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'This account has been deactivated. Please contact us for assistance.']);
            }

            $request->session()->regenerate();
            Auth::user()->forceFill(['last_login_at' => now()])->saveQuietly();

            // Always land on the role's own portal home. (Avoid intended() — a stale
            // cross-role intended URL, e.g. /admin/dashboard, would 403 the wrong role.)
            return match(Auth::user()->role) {
                'admin' => redirect('/admin/dashboard'),
                'staff' => redirect('/staff/dashboard'),
                default => redirect('/dashboard'),
            };
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
