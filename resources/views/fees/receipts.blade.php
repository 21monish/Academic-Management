<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Receipts</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Receipt No</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Fee</th><th class="px-4 py-3 text-left">Amount</th><th class="px-4 py-3 text-left">Collected By</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($receipts as $receipt)<tr><td class="px-4 py-3 font-semibold">{{ $receipt->receipt_no }}</td><td class="px-4 py-3">{{ $receipt->payment_date }}</td><td class="px-4 py-3">{{ $receipt->student?->enrollment_no }}</td><td class="px-4 py-3">{{ $receipt->ledger?->feeStructure?->feeCategory?->name }}</td><td class="px-4 py-3">{{ number_format($receipt->amount_paid, 2) }}</td><td class="px-4 py-3">{{ $receipt->collectedBy?->name }}</td></tr>@empty<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No receipts.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $receipts->links() }}</div>
    </div>
</x-app-layout>
