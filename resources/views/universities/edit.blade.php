<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit University</h2>
            <a href="{{ route('universities.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('universities.update', $university) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf @method('PUT')
            @include('universities._form')
            <div class="text-right">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Update</button>
            </div>
        </form>
    </div>
</x-app-layout>
