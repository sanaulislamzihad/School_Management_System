<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function studentForm(): View
    {
        return view('auth.login', ['portal' => 'student']);
    }

    public function staffForm(): View
    {
        return view('auth.login', ['portal' => 'staff']);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:200'],
            'portal' => ['required', 'in:student,staff'],
        ]);

        $key = 'login:'.Str::lower($data['login']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['login' => 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.']);
        }

        $roles = $data['portal'] === 'student' ? ['student'] : ['admin', 'teacher'];
        $signedIn = Auth::attempt([
            'login' => $data['login'], 'password' => $data['password'], 'active' => true,
            fn ($query) => $query->whereIn('role', $roles),
        ], $request->boolean('remember'));

        if (! $signedIn) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['login' => 'The ID or password is incorrect.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->homeRoute()));
    }

    public function logout(Request $request): RedirectResponse
    {
        $role = $request->user()->role;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($role === 'student' ? 'student.login' : 'staff.login');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => $data['password']]);
        $request->session()->regenerate();

        return back()->with('ok', 'Password updated.');
    }
}
