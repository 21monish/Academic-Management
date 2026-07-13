<x-app-layout>
    <x-slot name="header">
        @php
            $title = 'Subject Details';
        @endphp
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academic.subjects.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">Back to list</a>
                @if(hasPermission('subject.update'))
                    <a href="{{ route('academic.subjects.edit', $subject) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @include('partials._flash')

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <div class="text-xs font-medium text-gray-500 uppercase">Subject Code</div>
                    <div class="mt-1 text-gray-900">{{ $subject->code }}</div>
                </div>
                <div class="md:col-span-8">
                    <div class="text-xs font-medium text-gray-500 uppercase">Subject Name</div>
                    <div class="mt-1 text-gray-900">{{ $subject->name }}</div>
                </div>

                <div class="md:col-span-4">
                    <div class="text-xs font-medium text-gray-500 uppercase">Short Name</div>
                    <div class="mt-1 text-gray-900">{{ $subject->short_name }}</div>
                </div>
                <div class="md:col-span-4">
                    <div class="text-xs font-medium text-gray-500 uppercase">Type</div>
                    <div class="mt-1 text-gray-900">{{ $subject->type }}</div>
                </div>
                <div class="md:col-span-4">
                    <div class="text-xs font-medium text-gray-500 uppercase">Category</div>
                    <div class="mt-1 text-gray-900">{{ $subject->subject_category }}</div>
                </div>

                <div class="md:col-span-3">
                    <div class="text-xs font-medium text-gray-500 uppercase">Credits</div>
                    <div class="mt-1 text-gray-900">{{ $subject->credits ?? '—' }}</div>
                </div>
                <div class="md:col-span-3">
                    <div class="text-xs font-medium text-gray-500 uppercase">Theory Hours</div>
                    <div class="mt-1 text-gray-900">{{ $subject->theory_hours ?? '—' }}</div>
                </div>
                <div class="md:col-span-3">
                    <div class="text-xs font-medium text-gray-500 uppercase">Practical Hours</div>
                    <div class="mt-1 text-gray-900">{{ $subject->lab_hours ?? '—' }}</div>
                </div>
                <div class="md:col-span-3">
                    <div class="text-xs font-medium text-gray-500 uppercase">Tutorial Hours</div>
                    <div class="mt-1 text-gray-900">{{ $subject->tutorial_hours ?? '—' }}</div>
                </div>

                <div class="md:col-span-6">
                    <div class="text-xs font-medium text-gray-500 uppercase">Department</div>
                    <div class="mt-1 text-gray-900">{{ $subject->department?->name ?? '—' }}</div>
                </div>

                <div class="md:col-span-6">
                    <div class="text-xs font-medium text-gray-500 uppercase">Status</div>
                    <div class="mt-2">
                        @if($subject->is_active)
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs">Active</span>
                        @else
                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="text-sm text-gray-600">
                    Curriculum: {{ $subject->curriculum->count() }} item(s)
                </div>

                <div class="flex flex-wrap gap-3">
                    @if(hasPermission('subject.deactivate') && $subject->is_active)
                        <form action="{{ route('academic.subjects.deactivate', $subject) }}" method="POST" onsubmit="return confirm('Deactivate this subject?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-yellow-50 text-yellow-800 rounded-md text-sm border border-yellow-200">Deactivate</button>
                        </form>
                    @endif

                    @if(hasPermission('subject.activate') && !$subject->is_active)
                        <form action="{{ route('academic.subjects.activate', $subject) }}" method="POST" onsubmit="return confirm('Activate this subject?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-green-50 text-green-800 rounded-md text-sm border border-green-200">Activate</button>
                        </form>
                    @endif

                    @if(hasPermission('subject.delete'))
                        <form action="{{ route('academic.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Delete this subject?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-50 text-red-800 rounded-md text-sm border border-red-200">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

