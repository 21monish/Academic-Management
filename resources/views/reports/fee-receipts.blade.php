<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Fee Receipts</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
            <x-text-input name="receipt_no" :value="request('receipt_no')" placeholder="Receipt no" class="flex-1" />
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('reports.export', 'receipts') }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-center text-sm font-semibold text-white">Export CSV</a>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Receipt</th><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Amount</th><th class="px-4 py-3 text-left">Mode</th><th class="px-4 py-3 text-left">Print</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($receipts as $receipt)<tr><td class="px-4 py-3 font-semibold">{{ $receipt->receipt_no }}</td><td class="px-4 py-3">{{ $receipt->student?->enrollment_no }}</td><td class="px-4 py-3">{{ number_format($receipt->amount_paid, 2) }}</td><td class="px-4 py-3">{{ $receipt->payment_mode }} / {{ $receipt->payment_status }}</td><td class="px-4 py-3"><a class="font-semibold text-cyan-700" href="{{ route('reports.fee-receipts.print', $receipt) }}">Print</a></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No receipts found.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $receipts->links() }}</div>
    </div>
</x-app-layout>
