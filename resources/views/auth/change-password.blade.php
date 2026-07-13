<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Change Password</h2>
    </x-slot>

    <div class="py-8 max-w-md mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-md text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (auth()->user()->must_change_password)
            <div class="mb-4 p-3 bg-amber-100 text-amber-800 rounded-md text-sm">
                You must change your password before continuing.
            </div>
        @endif

        <form action="{{ route('password.change.update') }}" method="POST" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf @method('PUT')

            <div>
                <x-input-label for="current_password" value="Current Password" />
                <x-text-input id="current_password" name="current_password" type="password" class="block mt-1 w-full" required />
            </div>

            <div>
                <x-input-label for="password" value="New Password" />
                <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirm New Password" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" required />
            </div>

            <div class="text-right">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Update Password</button>
            </div>
        </form>
    </div>
</x-app-layout>
