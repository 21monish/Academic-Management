<x-app-layout>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Flash Messages --}}
    @include('partials.flash')

    <div class="bg-white rounded-lg shadow">

        {{-- Header --}}
        <div class="px-6 py-4 border-b">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-bold text-gray-800">
                    Create Semester
                </h2>
                <a href="{{ route('academic.semesters.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
            </div>

            <p class="text-sm text-gray-500 mt-1">
                Add a new semester to a programme.
            </p>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="mx-6 mt-6 rounded-md bg-red-50 border border-red-200 p-4">
                <div class="font-semibold text-red-700 mb-2">
                    Please fix the following errors:
                </div>

                <ul class="list-disc list-inside text-red-600 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form
            action="{{ route('academic.semesters.store') }}"
            method="POST"
            class="p-6">

            @csrf

            @include('semester._form')

        </form>

    </div>

</div>
</x-app-layout>
