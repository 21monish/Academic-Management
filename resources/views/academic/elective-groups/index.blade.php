<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Elective Groups</h2>
            <a href="{{ route('academic.elective-groups.create') }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Add Group</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Group</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Curriculum</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Select</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($electiveGroups as $group)
                        <tr>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $group->group_name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $group->curriculum?->programme?->name }} / Sem {{ $group->curriculum?->semester?->semester_no }} / {{ $group->curriculum?->subject?->code }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $group->select_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('academic.elective-groups.edit', $group) }}" class="text-sm font-semibold text-cyan-700">Edit</a>
                                <form method="POST" action="{{ route('academic.elective-groups.destroy', $group) }}" class="inline" onsubmit="return confirm('Delete this elective group?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ms-3 text-sm font-semibold text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">No elective groups found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $electiveGroups->links() }}</div>
    </div>
</x-app-layout>
