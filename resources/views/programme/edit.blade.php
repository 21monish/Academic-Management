<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Programme</h2>
            <a href="{{ route('academic.programmes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">Back</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @include('programme._form', ['programme' => $programme])
    </div>
</x-app-layout>

