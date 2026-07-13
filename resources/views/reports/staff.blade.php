<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Staff Reports</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
            <x-text-input name="q" :value="request('q')" placeholder="Employee code or name" class="flex-1" />
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('reports.export', 'staff') }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-center text-sm font-semibold text-white">Export CSV</a>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">Staff</th><th class="px-4 py-3 text-left">Department</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Print</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($staff as $member)<tr><td class="px-4 py-3 font-semibold">{{ $member->employee_code }}</td><td class="px-4 py-3">{{ $member->first_name }} {{ $member->last_name }}</td><td class="px-4 py-3">{{ $member->department?->name ?? '-' }}</td><td class="px-4 py-3">{{ $member->staff_type }} / {{ $member->employment_type }}</td><td class="px-4 py-3"><a class="font-semibold text-cyan-700" href="{{ route('reports.staff.print', $member) }}">Print</a></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No staff found.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $staff->links() }}</div>
    </div>
</x-app-layout>
