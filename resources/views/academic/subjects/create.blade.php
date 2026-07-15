<x-app-layout>
    <x-slot name="header">
        @php
            $title = 'Create Subject';
        @endphp
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
            <a href="{{ route('academic.subjects.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">Back to list</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">

        @php
            $departments = $departments ?? [];
            $programmes = $programmes ?? [];
            $semesters = $semesters ?? [];
            $subjectTypes = $subjectTypes ?? ['Theory' => 'Theory', 'Lab' => 'Lab', 'Tutorial' => 'Tutorial'];
            $categories = $categories ?? ['Core' => 'Core', 'Elective' => 'Elective', 'Open Elective' => 'Open Elective', 'Audit' => 'Audit'];
            $submitLabel = 'Create Subject';
            $cancelUrl = route('academic.subjects.index');
            $actionRoute = route('academic.subjects.store');
            $methodOverride = null;
        @endphp

        @include('academic.subjects._form')

    </div>
</x-app-layout>

