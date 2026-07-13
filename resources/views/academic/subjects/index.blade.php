<x-app-layout>
    <x-slot name="header">
        @php
            $title = 'Subjects';
        @endphp

        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>

            @if(Route::has('academic.subjects.create') && hasPermission('subject.create'))
                <a href="{{ route('academic.subjects.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                    + Add Subject
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @include('partials._flash')

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 mb-6 p-4">
            <form method="GET" action="{{ route('academic.subjects.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-4">
                    <x-input-label for="q" value="Search" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="Code / Name / Short Name" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="dept_id" value="Department" />
                    <select id="dept_id" name="dept_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->dept_id }}" @selected((string)request('dept_id') === (string)$dept->dept_id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="type" value="Type" />
                    <select id="type" name="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach(['Theory','Lab','Tutorial'] as $t)
                            <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="is_active" value="Status" />
                    <select id="is_active" name="is_active" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        <option value="1" @selected(request('is_active') === '1')>Active</option>
                        <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-1">
                    <x-input-label for="sort" value="Sort" />
                    <select id="sort" name="sort" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        @foreach($sortable as $field => $label)
                            <option value="{{ $field }}" @selected(request('sort', 'code') === $field)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1">
                    <x-input-label for="direction" value="Dir" />
                    <select id="direction" name="direction" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="desc" @selected(request('direction', 'asc') === 'desc')>Desc</option>
                        <option value="asc" @selected(request('direction', 'asc') === 'asc')>Asc</option>
                    </select>
                </div>

                <div class="flex items-end md:col-span-12">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white rounded-md text-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="text-sm text-gray-600">{{ $subjects->total() }} result(s)</div>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Short Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Credits</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($subjects as $subject)
                        <tr>
                            <td class="px-4 py-2">{{ $subject->code }}</td>
                            <td class="px-4 py-2">{{ $subject->name }}</td>
                            <td class="px-4 py-2">{{ $subject->short_name }}</td>
                            <td class="px-4 py-2">{{ $subject->type }}</td>
                            <td class="px-4 py-2">{{ $subject->subject_category }}</td>
                            <td class="px-4 py-2">{{ $subject->credits ?? '—' }}</td>
                            <td class="px-4 py-2">
                                @if($subject->is_active)
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs">Active</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs">Inactive</span>
                                @endif
                            </td>

                            <td class="px-4 py-2 text-right space-x-2">
                                @if(hasPermission('subject.view'))
                                    <a href="{{ route('academic.subjects.show', $subject) }}" class="text-indigo-600 text-sm">View</a>
                                @endif

                                @if(hasPermission('subject.update'))
                                    <a href="{{ route('academic.subjects.edit', $subject) }}" class="text-indigo-600 text-sm">Edit</a>
                                @endif

                                @if(hasPermission('subject.deactivate') && $subject->is_active)
                                    <form action="{{ route('academic.subjects.deactivate', $subject) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this subject?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-yellow-700 text-sm">Deactivate</button>
                                    </form>
                                @endif

                                @if(hasPermission('subject.activate') && !$subject->is_active)
                                    <form action="{{ route('academic.subjects.activate', $subject) }}" method="POST" class="inline" onsubmit="return confirm('Activate this subject?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-green-700 text-sm">Activate</button>
                                    </form>
                                @endif

                                @if(hasPermission('subject.delete'))
                                    <form action="{{ route('academic.subjects.destroy', $subject) }}" method="POST" class="inline" onsubmit="return confirm('Delete this subject?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 text-sm">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500">No subjects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $subjects->links() }}</div>
    </div>
</x-app-layout>

