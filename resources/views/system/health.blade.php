<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold text-slate-900">System Health</h2>
            <p class="text-sm text-slate-500">Super Admin diagnostics and recent Laravel errors.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400">Checks</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $summary['total'] }}</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-emerald-700">Passing</p>
                <p class="mt-2 text-3xl font-black text-emerald-800">{{ $summary['passing'] }}</p>
            </div>
            <div class="rounded-lg border {{ $summary['failing'] ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-white' }} p-4 shadow-sm">
                <p class="text-xs font-bold uppercase {{ $summary['failing'] ? 'text-red-700' : 'text-slate-400' }}">Needs Attention</p>
                <p class="mt-2 text-3xl font-black {{ $summary['failing'] ? 'text-red-800' : 'text-slate-900' }}">{{ $summary['failing'] }}</p>
            </div>
        </div>

        <div class="mb-6 grid gap-6 xl:grid-cols-2">
            @foreach ($checks as $group => $groupChecks)
                <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <h3 class="text-sm font-bold text-slate-900">{{ $group }}</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($groupChecks as $check)
                            <div class="p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-bold text-slate-900">{{ $check['label'] }}</h4>
                                    <span class="rounded-md px-2 py-1 text-xs font-bold {{ $check['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $check['status'] }}
                                    </span>
                                </div>
                                <p class="mt-2 break-words text-xs leading-5 text-slate-600">{{ $check['detail'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Recent Error Log</h3>
                    <p class="mt-1 break-all text-xs text-slate-500">{{ $logPath }}</p>
                </div>
                <span class="rounded-md bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">Size: {{ $logSize }}</span>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Time</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Level</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($errors as $error)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ $error['date'] }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-md bg-red-50 px-2 py-1 text-xs font-bold text-red-700">{{ $error['level'] }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $error['message'] }}</div>
                                @if ($error['trace'])
                                    <details class="mt-3">
                                        <summary class="cursor-pointer text-xs font-bold text-cyan-700">View trace</summary>
                                        <pre class="mt-2 max-h-80 overflow-auto rounded-lg bg-slate-950 p-3 text-xs leading-5 text-slate-100">{{ $error['trace'] }}</pre>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center text-slate-500">No recent errors found in the Laravel log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
