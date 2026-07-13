<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Department</h2>
            <a href="{{ route('departments.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('departments.update', $department) }}" method="POST" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf @method('PUT')
            @include('departments._form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('departments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Update</button>
            </div>
        </form>
    </div>
</x-app-layout>
