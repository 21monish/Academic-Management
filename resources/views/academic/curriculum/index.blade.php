<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Curriculum</h2>
            <a href="{{ route('academic.curriculum.create') }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Add Subject</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Programme</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Semester</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Marks</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($curriculumItems as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $item->programme?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">Sem {{ $item->semester?->semester_no ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $item->subject?->code }} - {{ $item->subject?->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $item->is_mandatory ? 'Mandatory' : 'Elective' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $item->min_passing_marks ?? '-' }} / {{ $item->max_marks ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('academic.curriculum.edit', $item) }}" class="text-sm font-semibold text-cyan-700">Edit</a>
                                <form method="POST" action="{{ route('academic.curriculum.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this curriculum item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ms-3 text-sm font-semibold text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No curriculum records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $curriculumItems->links() }}</div>
    </div>
</x-app-layout>
