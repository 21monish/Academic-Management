<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Role</h2>
            <a href="{{ route('roles.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                @include('roles._form')
            </form>
        </div>
    </div>
</x-app-layout>
