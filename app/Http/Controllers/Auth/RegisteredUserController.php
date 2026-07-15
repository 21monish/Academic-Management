<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use App\Support\ValidationRules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['nullable', 'string', 'max:80', 'unique:'.User::class],
            'name' => ValidationRules::shortText(false, 80),
            'email' => [...ValidationRules::email(true, 150), 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $username = $request->username ?: ($request->name ?: str($request->email)->before('@')->value());
        $studentRole = UserRole::where('role_name', 'Student')->first();

        $user = User::create([
            'role_id' => $studentRole?->role_id,
            'username' => $username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'is_active' => true,
            'is_verified' => false,
            'must_change_password' => false,
        ]);
        $user->permissions()->sync($studentRole?->permissions()->pluck('permissions.permission_id')->all() ?? []);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
