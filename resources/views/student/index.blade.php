<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Students</h2>
            <a href="{{ route('students.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Add Student</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6 p-4 border border-gray-100">
            <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <x-input-label for="q" value="Search" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="Admission/Name/Email/Mobile" />
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
                    <x-input-label for="programme_id" value="Programme" />
                    <select id="programme_id" name="programme_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach($programmes as $programme)
                            <option value="{{ $programme->programme_id }}" @selected((string)request('programme_id') === (string)$programme->programme_id)>{{ $programme->name ?? $programme->programme_name ?? $programme->programme_id }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="category_id" value="Category" />
                    <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->category_id }}" @selected((string)request('category_id') === (string)$category->category_id)>{{ $category->name ?? $category->category_name ?? $category->category_id }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" name="gender" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach(['Male','Female','Other'] as $g)
                            <option value="{{ $g }}" @selected((string)request('gender') === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="student_type" value="Student Type" />
                    <select id="student_type" name="student_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach(['Regular','D2D','C2D'] as $type)
                            <option value="{{ $type }}" @selected((string)request('student_type') === $type)>{{ $type }}</option>
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

                <div class="flex items-end md:col-span-6">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white rounded-md text-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Enrollment No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Photo</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Programme</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">College</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mobile</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($students as $s)
                        <tr>
                            <td class="px-4 py-2">{{ $s->enrollment_no }}</td>
                            <td class="px-4 py-2">
                                @if($s->photo_url)
                                    @php($photoSrc = \Illuminate\Support\Str::startsWith($s->photo_url, ['http://', 'https://', '/']) ? $s->photo_url : asset($s->photo_url))
                                    <img src="{{ $photoSrc }}" alt="Photo" class="h-10 w-10 rounded-full object-cover" />
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $s->first_name }} {{ $s->last_name }}</td>
                            <td class="px-4 py-2">{{ $s->student_type ?? 'Regular' }}</td>
                            <td class="px-4 py-2">{{ $s->gender ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $s->programme?->name ?? $s->programme?->programme_name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $s->college?->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $s->email ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $s->phone ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $s->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('students.show', $s) }}" class="text-indigo-600 text-sm">View</a>
                                <a href="{{ route('students.edit', $s) }}" class="text-indigo-600 text-sm">Edit</a>

                                @if($s->is_active)
                                    <form action="{{ route('students.deactivate', $s) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this student?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-yellow-700 text-sm">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('students.activate', $s) }}" method="POST" class="inline" onsubmit="return confirm('Activate this student?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-green-700 text-sm">Activate</button>
                                    </form>
                                @endif

                                <form action="{{ route('students.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Delete this student?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="px-4 py-6 text-center text-gray-500">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $students->links() }}</div>
    </div>
</x-app-layout>

