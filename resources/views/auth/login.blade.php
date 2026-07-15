<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Account Type -->
        <div>
            <x-input-label for="account_type" :value="__('Login As')" />
            <select id="account_type" name="account_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="administration" @selected(old('account_type', request('type', 'administration')) === 'administration')>{{ __('Administration') }}</option>
                <option value="staff" @selected(old('account_type', request('type')) === 'staff')>{{ __('Staff') }}</option>
                <option value="student" @selected(old('account_type', request('type')) === 'student')>{{ __('Student') }}</option>
            </select>
            <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
        <x-input-label for="username" :value="__('Username')" />
        <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
        <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
