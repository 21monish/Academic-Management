<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Fee Reports</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-400">Demand</p><p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalDemand, 2) }}</p></div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-400">Collected</p><p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($totalCollected, 2) }}</p></div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-400">Balance</p><p class="mt-2 text-2xl font-bold text-red-600">{{ number_format($totalBalance, 2) }}</p></div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-400">Overdue Ledgers</p><p class="mt-2 text-2xl font-bold text-slate-900">{{ $overdueCount }}</p></div>
        </div>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Ledgers</th><th class="px-4 py-3 text-left">Balance</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($statusRows as $row)<tr><td class="px-4 py-3">{{ $row->payment_status }}</td><td class="px-4 py-3">{{ $row->ledgers }}</td><td class="px-4 py-3">{{ number_format($row->balance ?? 0, 2) }}</td></tr>@empty<tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">No report data.</td></tr>@endforelse</tbody></table></div>
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Receipt</th><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Amount</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($recentPayments as $payment)<tr><td class="px-4 py-3">{{ $payment->receipt_no }}</td><td class="px-4 py-3">{{ $payment->student?->enrollment_no }}</td><td class="px-4 py-3">{{ number_format($payment->amount_paid, 2) }}</td></tr>@empty<tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">No recent payments.</td></tr>@endforelse</tbody></table></div>
        </div>
    </div>
</x-app-layout>
