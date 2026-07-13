<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Activity Logs</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
            <x-text-input name="q" :value="request('q')" placeholder="Search route, URL, method, or user" class="flex-1" />
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('reports.export', 'activity') }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-center text-sm font-semibold text-white">Export CSV</a>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3 text-left">Method</th><th class="px-4 py-3 text-left">Route</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">When</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse($logs as $log)<tr><td class="px-4 py-3">{{ $log->user?->username ?? 'System' }}</td><td class="px-4 py-3 font-semibold">{{ $log->method }}</td><td class="px-4 py-3"><div class="font-semibold">{{ $log->route_name ?? '-' }}</div><div class="text-xs text-slate-500">{{ $log->url }}</div></td><td class="px-4 py-3">{{ $log->status_code }}</td><td class="px-4 py-3">{{ $log->created_at?->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No activity yet.</td></tr>@endforelse</tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</x-app-layout>
