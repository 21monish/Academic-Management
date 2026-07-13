<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function show(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password_hash' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->intended(route('dashboard'))->with('status', 'Password updated successfully.');
    }
}
