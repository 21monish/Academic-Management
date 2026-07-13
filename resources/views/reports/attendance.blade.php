<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Attendance Reports</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
            <x-text-input name="threshold" type="number" step="0.01" :value="$threshold" placeholder="Below %" />
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('reports.export', 'attendance') }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-center text-sm font-semibold text-white">Export CSV</a>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Semester</th><th class="px-4 py-3 text-left">Lectures</th><th class="px-4 py-3 text-left">Attendance</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($summaries as $summary)<tr><td class="px-4 py-3">{{ $summary->student?->enrollment_no }} - {{ $summary->student?->first_name }}</td><td class="px-4 py-3">{{ $summary->subject?->code }}</td><td class="px-4 py-3">Sem {{ $summary->semester?->semester_no }}</td><td class="px-4 py-3">{{ $summary->attended_lectures }}/{{ $summary->total_lectures }}</td><td class="px-4 py-3 font-semibold {{ $summary->is_detained ? 'text-red-600' : 'text-emerald-700' }}">{{ $summary->attendance_percentage }}%</td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No attendance rows.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $summaries->links() }}</div>
    </div>
</x-app-layout>
