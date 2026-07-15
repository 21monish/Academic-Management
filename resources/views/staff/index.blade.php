<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Staff</h2>
            <div class="flex flex-wrap gap-2">
                @if(hasPermission('staff_assignment.view'))
                    <a href="{{ route('attendance.assignments') }}" class="px-4 py-2 bg-cyan-700 text-white rounded-md text-sm">Subject Assignments</a>
                @endif
                @if(hasPermission('staff.create'))
                    <a href="{{ route('staff.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Add Staff</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6 p-4 border border-gray-100">
            <form method="GET" action="{{ route('staff.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <x-input-label for="staff_type" value="Staff Type" />
                    <select id="staff_type" name="staff_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach(['Teaching','Non-Teaching','Both'] as $t)
                            <option value="{{ $t }}" @selected(request('staff_type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="employment_type" value="Employment Type" />
                    <select id="employment_type" name="employment_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach(['Permanent','Contractual','Visiting'] as $t)
                            <option value="{{ $t }}" @selected(request('employment_type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="college_id" value="College" />
                    <select id="college_id" name="college_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->college_id }}" @selected((string)request('college_id') === (string)$college->college_id)>{{ $college->name }}</option>
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

                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white rounded-md text-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employment</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">College</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dept</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($staff as $s)
                        <tr>
                            <td class="px-4 py-2">{{ $s->employee_code }}</td>
                            <td class="px-4 py-2">{{ $s->first_name }} {{ $s->last_name }}</td>
                            <td class="px-4 py-2">{{ $s->staff_type }}</td>
                            <td class="px-4 py-2">{{ $s->employment_type }}</td>
                            <td class="px-4 py-2">{{ $s->email }}</td>
                            <td class="px-4 py-2">{{ $s->college?->name }}</td>
                            <td class="px-4 py-2">{{ $s->department?->name }}</td>
                            <td class="px-4 py-2">{{ $s->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                @if(in_array($s->staff_type, ['Teaching', 'Both'], true) && hasPermission('staff_assignment.view'))
                                    <a href="{{ route('attendance.assignments', ['staff_id' => $s->staff_id]) }}" class="text-cyan-700 text-sm">Assign</a>
                                @endif
                                <a href="{{ route('staff.edit', $s) }}" class="text-indigo-600 text-sm">Edit</a>
                                <form action="{{ route('staff.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Delete this staff?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">No staff found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $staff->links() }}</div>
    </div>
</x-app-layout>

