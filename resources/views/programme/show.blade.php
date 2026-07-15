<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Programme Details</h2>
            <div class="flex gap-2">
                <a href="{{ route('academic.programmes.edit', $programme) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Edit</a>
                <a href="{{ route('academic.programmes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-sm text-gray-500">Programme ID</div>
                    <div class="text-2xl font-semibold text-gray-900">{{ $programme->programme_id }}</div>
                    <div class="mt-2 text-xl font-semibold text-gray-900">{{ $programme->name }}</div>
                    <div class="text-sm text-gray-600">Code: <span class="font-medium">{{ $programme->code }}</span></div>
                </div>

                <div class="text-right">
                    <div class="text-sm text-gray-600">Status: <span class="font-medium">{{ $programme->is_active ? 'Active' : 'Inactive' }}</span></div>
                    <div class="text-sm text-gray-600">Level: <span class="font-medium">{{ $programme->level }}</span></div>
                    <div class="text-sm text-gray-600">Department: <span class="font-medium">{{ $programme->department?->name ?? '—' }}</span></div>
                </div>
            </div>

            <hr class="my-6" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Duration</div>
                    <div class="mt-2 text-sm text-gray-600">{{ $programme->duration_semesters ?? '—' }} semesters</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-900">Credits</div>
                    <div class="mt-2 text-sm text-gray-600">{{ $programme->total_credits ?? '—' }}</div>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                @if($programme->is_active)
                    <form action="{{ route('academic.programmes.deactivate', $programme) }}" method="POST" onsubmit="return confirm('Deactivate this programme?');" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md text-sm">Deactivate</button>
                    </form>
                @else
                    <form action="{{ route('academic.programmes.activate', $programme) }}" method="POST" onsubmit="return confirm('Activate this programme?');" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-green-100 text-green-800 rounded-md text-sm">Activate</button>
                    </form>
                @endif

                <form action="{{ route('academic.programmes.destroy', $programme) }}" method="POST" onsubmit="return confirm('Delete this programme?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-100 text-red-800 rounded-md text-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

