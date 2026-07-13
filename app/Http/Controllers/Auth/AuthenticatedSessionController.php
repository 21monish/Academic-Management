<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AccessScopeService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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
    public function store(LoginRequest $request, AccessScopeService $accessScope): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if (! $user) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('access_scope', $accessScope->forUser($user));

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            Auth::guard('web')->logout();
        } catch (QueryException) {
            // If the database is unavailable, still clear the local session below.
        }

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
