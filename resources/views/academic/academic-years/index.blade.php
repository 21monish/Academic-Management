<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Academic Years</h2>
            <a href="{{ route('academic.academic-years.create') }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Add Year</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Year</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">College</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Dates</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($academicYears as $year)
                        <tr>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $year->label }} @if($year->is_current)<span class="ms-2 rounded-full bg-cyan-50 px-2 py-1 text-xs text-cyan-700">Current</span>@endif</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $year->college?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $year->start_date?->format('d M Y') ?? '-' }} to {{ $year->end_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $year->status }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('academic.academic-years.edit', $year) }}" class="text-sm font-semibold text-cyan-700">Edit</a>
                                <form method="POST" action="{{ route('academic.academic-years.destroy', $year) }}" class="inline" onsubmit="return confirm('Delete this academic year?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ms-3 text-sm font-semibold text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">No academic years found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $academicYears->links() }}</div>
    </div>
</x-app-layout>
