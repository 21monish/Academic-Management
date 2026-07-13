<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Colleges</h2>
            <a href="{{ route('colleges.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Add College</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
        @endif

        @php($showUniversityFilter = ! ($accessScope['university_id'] ?? auth()->user()?->university_id))

        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6 p-4 border border-gray-100">
            <form method="GET" action="{{ route('colleges.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="{{ $showUniversityFilter ? 'md:col-span-2' : 'md:col-span-3' }}">
                    <x-input-label for="q" value="Search" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="Code, name, email, phone" />
                </div>

                @if($showUniversityFilter)
                    <div>
                        <x-input-label for="university_id" value="University" />
                        <select id="university_id" name="university_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">All</option>
                            @foreach($universities as $university)
                                <option value="{{ $university->university_id }}" @selected((string)request('university_id') === (string)$university->university_id)>{{ $university->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <x-input-label for="affiliation_type" value="Affiliation" />
                    <select id="affiliation_type" name="affiliation_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach(['Autonomous','Affiliated','Constituent'] as $type)
                            <option value="{{ $type }}" @selected(request('affiliation_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="college_type" value="Type" />
                    <select id="college_type" name="college_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All</option>
                        @foreach(['Government','Private','Grant-in-Aid'] as $type)
                            <option value="{{ $type }}" @selected(request('college_type') === $type)>{{ $type }}</option>
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

                <div class="flex items-end gap-2 md:col-span-6">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white rounded-md text-sm">Filter</button>
                    @if(request()->hasAny(['q', 'university_id', 'affiliation_type', 'college_type', 'is_active']))
                        <a href="{{ route('colleges.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">University</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Departments</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($colleges as $college)
                        <tr>
                            <td class="px-4 py-2">{{ $college->code }}</td>
                            <td class="px-4 py-2">{{ $college->name }}</td>
                            <td class="px-4 py-2">{{ $college->university?->name }}</td>
                            <td class="px-4 py-2">{{ $college->departments_count }}</td>
                            <td class="px-4 py-2">{{ $college->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('colleges.edit', $college) }}" class="text-indigo-600 text-sm">Edit</a>
                                <form action="{{ route('colleges.destroy', $college) }}" method="POST" class="inline" onsubmit="return confirm('Delete this college?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No colleges found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $colleges->links() }}</div>
    </div>
</x-app-layout>
