<x-app-layout>
    <x-slot name="header">
        @php
            $title = 'Programmes';
        @endphp
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
            <a href="{{ route('academic.programmes.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Add Programme</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 mb-6 p-4">
            <form method="GET" action="{{ route('academic.programmes.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-4">
                    <x-input-label for="q" value="Search" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="Code / Name" />
                </div>

                <div>
                    <x-input-label for="dept_id" value="Department" />
                    <select id="dept_id" name="dept_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->dept_id }}" @selected((string)request('dept_id') === (string)$dept->dept_id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="level" value="Level" />
                    <select id="level" name="level" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach(['UG','PG','Diploma','PhD'] as $lvl)
                            <option value="{{ $lvl }}" @selected(request('level') === $lvl)>{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="is_active" value="Status" />
                    <select id="is_active" name="is_active" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        <option value="1" @selected(request('is_active') === '1')>Active</option>
                        <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="sort" value="Sort" />
                    <select id="sort" name="sort" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        @foreach($sortable as $field => $label)
                            <option value="{{ $field }}" @selected(request('sort') === $field)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="direction" value="Direction" />
                    <select id="direction" name="direction" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="desc" @selected(request('direction', 'desc') === 'desc')>Desc</option>
                        <option value="asc" @selected(request('direction') === 'asc')>Asc</option>
                    </select>
                </div>

                <div class="flex items-end md:col-span-12">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white rounded-md text-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="text-sm text-gray-600">{{ $programmes->total() }} result(s)</div>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration (Semesters)</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Credits</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($programmes as $programme)
                        <tr>
                            <td class="px-4 py-2">{{ $programme->code }}</td>
                            <td class="px-4 py-2">{{ $programme->name }}</td>
                            <td class="px-4 py-2">{{ $programme->level }}</td>
                            <td class="px-4 py-2">{{ $programme->department?->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $programme->duration_semesters ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $programme->total_credits ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $programme->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('academic.programmes.show', $programme) }}" class="text-indigo-600 text-sm">View</a>
                                <a href="{{ route('academic.programmes.edit', $programme) }}" class="text-indigo-600 text-sm">Edit</a>

                                @if($programme->is_active)
                                    <form action="{{ route('academic.programmes.deactivate', $programme) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this programme?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-yellow-700 text-sm">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('academic.programmes.activate', $programme) }}" method="POST" class="inline" onsubmit="return confirm('Activate this programme?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-green-700 text-sm">Activate</button>
                                    </form>
                                @endif

                                <form action="{{ route('academic.programmes.destroy', $programme) }}" method="POST" class="inline" onsubmit="return confirm('Delete this programme?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500">No programmes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $programmes->links() }}</div>
    </div>
</x-app-layout>

