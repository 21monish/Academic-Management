<x-app-layout>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Flash Messages --}}
    @include('partials.flash')

    <div class="bg-white rounded-lg shadow">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b">

            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Semester Details
                </h2>

                <p class="text-sm text-gray-500">
                    View semester information.
                </p>
            </div>

            <div class="flex gap-2">

                <a href="{{ route('academic.semesters.edit', $semester) }}"
                   class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                    Edit
                </a>

                <a href="{{ route('academic.semesters.index') }}"
                   class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
                    Back
                </a>

            </div>

        </div>

        {{-- Details --}}
        <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm font-semibold text-gray-600">
                        Programme
                    </label>

                    <div class="mt-1 text-gray-900">
                        {{ $semester->programme->name ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600">
                        Semester Name
                    </label>

                    <div class="mt-1 text-gray-900">
                        {{ $semester->name }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600">
                        Semester Number
                    </label>

                    <div class="mt-1 text-gray-900">
                        {{ $semester->semester_no }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600">
                        Status
                    </label>

                    <div class="mt-1">

                        @if($semester->is_active)

                            <span class="inline-flex px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm">
                                Active
                            </span>

                        @else

                            <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm">
                                Inactive
                            </span>

                        @endif

                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600">
                        Created At
                    </label>

                    <div class="mt-1 text-gray-900">
                        {{ optional($semester->created_at)->format('d M Y h:i A') }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600">
                        Last Updated
                    </label>

                    <div class="mt-1 text-gray-900">
                        {{ optional($semester->updated_at)->format('d M Y h:i A') }}
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
</x-app-layout>
