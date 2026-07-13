<x-app-layout>
    <x-slot name="header">
        @php
            $title = 'Semesters';
        @endphp

        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $title }}
            </h2>

            <a href="{{ route('academic.semesters.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                + Add Semester
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">

        @include('partials._flash')

        {{-- Search Card --}}
        <div class="bg-white shadow-sm rounded-lg border border-gray-100 mb-6 p-4">

            <form method="GET"
                  action="{{ route('academic.semesters.index') }}"
                  class="grid grid-cols-1 md:grid-cols-12 gap-3">

                <div class="md:col-span-4">
                    <x-input-label
                        for="search"
                        value="Search" />

                    <x-text-input
                        id="search"
                        name="search"
                        class="block mt-1 w-full"
                        :value="request('search')"
                        placeholder="Semester Name / Number" />
                </div>

                <div>
                    <x-input-label
                        for="programme_id"
                        value="Programme" />

                    <select
                        id="programme_id"
                        name="programme_id"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">

                        <option value="">All</option>

                        @foreach($programmes as $programme)

                            <option value="{{ $programme->id }}"
                                @selected(request('programme_id') == $programme->id)>

                                {{ $programme->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div>
                    <x-input-label
                        for="is_active"
                        value="Status" />

                    <select
                        id="is_active"
                        name="is_active"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">

                        <option value="">All</option>

                        <option value="1"
                            @selected(request('is_active') === '1')>

                            Active

                        </option>

                        <option value="0"
                            @selected(request('is_active') === '0')>

                            Inactive

                        </option>

                    </select>

                </div>

                <div class="flex items-end">

                    <button
                        type="submit"
                        class="w-full px-4 py-2 bg-gray-900 text-white rounded-md text-sm">

                        Filter

                    </button>

                </div>

            </form>

        </div>

        {{-- Table --}}
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">

            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">

                <div class="text-sm text-gray-600">

                    {{ $semesters->total() }} result(s)

                </div>

            </div>

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                            #
                        </th>

                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                            Semester
                        </th>

                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                            Semester No
                        </th>

                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                            Programme
                        </th>

                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                            Status
                        </th>

                        <th class="px-4 py-2"></th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($semesters as $semester)

                        <tr>

                            <td class="px-4 py-2">
                                {{ $loop->iteration + ($semesters->firstItem() ?? 0) - 1 }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $semester->name }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $semester->semester_no }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $semester->programme?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-2">

                                @if($semester->is_active)

                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs">

                                        Active

                                    </span>

                                @else

                                    <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs">

                                        Inactive

                                    </span>

                                @endif

                            </td>

                            <td class="px-4 py-2 text-right space-x-2">

                                <a href="{{ route('academic.semesters.show', $semester) }}"
                                   class="text-indigo-600 text-sm">

                                    View

                                </a>

                                <a href="{{ route('academic.semesters.edit', $semester) }}"
                                   class="text-indigo-600 text-sm">

                                    Edit

                                </a>

                                @if($semester->is_active)

                                    <form
                                        action="{{ route('academic.semesters.deactivate', $semester) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Deactivate this semester?')">

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="text-yellow-700 text-sm">

                                            Deactivate

                                        </button>

                                    </form>

                                @else

                                    <form
                                        action="{{ route('academic.semesters.activate', $semester) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Activate this semester?')">

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="text-green-700 text-sm">

                                            Activate

                                        </button>

                                    </form>

                                @endif

                                <form
                                    action="{{ route('academic.semesters.destroy', $semester) }}"
                                    method="POST"
                                    class="inline"
                                    onsubmit="return confirm('Delete this semester?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-red-600 text-sm">

                                        Delete

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="px-4 py-6 text-center text-gray-500">

                                No semesters found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-4">

            {{ $semesters->links() }}

        </div>

    </div>
</x-app-layout>
