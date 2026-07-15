<x-app-layout>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="bg-white rounded-lg shadow">

        {{-- Header --}}
        <div class="px-6 py-4 border-b">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-2xl font-bold text-gray-800">
                    Edit Semester
                </h2>
                <a href="{{ route('academic.semesters.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
            </div>

            <p class="text-sm text-gray-500 mt-1">
                Update semester information.
            </p>
        </div>

        {{-- Form --}}
        <form
            action="{{ route('academic.semesters.update', $semester) }}"
            method="POST"
            class="p-6">

            @csrf
            @method('PUT')

            @include('semester._form')

        </form>

    </div>

</div>
</x-app-layout>
