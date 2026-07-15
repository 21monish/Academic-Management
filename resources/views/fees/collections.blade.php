<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Fee Collection</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <div class="mb-6 grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
            <form method="GET" action="{{ route('fees.collections') }}" class="grid grid-cols-1 gap-3 rounded-lg border border-cyan-100 bg-cyan-50 p-4 shadow-sm md:grid-cols-4">
                <div class="md:col-span-4">
                    <h3 class="text-base font-bold text-slate-900">UPI QR Payment</h3>
                    <p class="mt-1 text-sm text-cyan-800">Generate a payment QR for a student ledger. After payment, enter the UTR below as an Online collection.</p>
                </div>
                <select name="qr_ledger_id" class="rounded-md border-gray-300 md:col-span-2" required>
                    <option value="">Select ledger for QR</option>
                    @foreach($ledgersList as $ledger)
                        <option value="{{ $ledger->ledger_id }}" @selected((string) request('qr_ledger_id') === (string) $ledger->ledger_id)>{{ $ledger->student?->enrollment_no }} / Balance {{ number_format($ledger->balance_due, 2) }}</option>
                    @endforeach
                </select>
                <x-text-input name="qr_amount" type="number" step="0.01" min="0.01" :value="request('qr_amount')" placeholder="Amount, defaults to balance" />
                <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Generate QR</button>
            </form>

            <div class="rounded-lg border border-slate-200 bg-white p-4 text-center shadow-sm">
                @if($paymentQr && $paymentQr['qr_url'])
                    <img src="{{ $paymentQr['qr_url'] }}" alt="UPI payment QR code" class="mx-auto h-56 w-56 rounded-md border border-slate-200 bg-white p-2">
                    <div class="mt-3 text-sm font-bold text-slate-900">{{ $paymentQr['ledger']->student?->enrollment_no }}</div>
                    <div class="text-xs text-slate-500">{{ $paymentQr['university']?->name ?? $paymentQr['upi_name'] }}</div>
                    <div class="text-xs text-slate-500">{{ $paymentQr['upi_id'] }}</div>
                    <div class="text-sm text-slate-600">INR {{ number_format($paymentQr['amount'], 2) }}</div>
                    <a href="{{ $paymentQr['upi_uri'] }}" class="mt-3 inline-flex rounded-md bg-slate-900 px-3 py-2 text-xs font-bold text-white">Open UPI App</a>
                @elseif($paymentQr)
                    <div class="text-sm font-semibold text-red-700">UPI ID not configured.</div>
                    <p class="mt-2 text-xs text-slate-500">Add UPI ID in the selected student's university settings.</p>
                @else
                    <div class="flex h-56 items-center justify-center rounded-md border border-dashed border-slate-300 text-sm text-slate-500">QR appears here</div>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('fees.collections.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="ledger_id" class="rounded-md border-gray-300" required><option value="">Ledger</option>@foreach($ledgersList as $ledger)<option value="{{ $ledger->ledger_id }}">{{ $ledger->student?->enrollment_no }} / Balance {{ number_format($ledger->balance_due, 2) }}</option>@endforeach</select>
            <select name="student_id" class="rounded-md border-gray-300" required><option value="">Student</option>@foreach($students as $student)<option value="{{ $student->student_id }}">{{ $student->enrollment_no }} - {{ $student->first_name }}</option>@endforeach</select>
            <x-text-input name="amount_paid" type="number" step="0.01" placeholder="Amount paid" required />
            <x-text-input name="payment_date" type="date" />
            <select name="payment_mode" class="rounded-md border-gray-300" required><option value="">Mode</option>@foreach(['Cash','Online','Cheque','DD','NEFT'] as $mode)<option @selected(old('payment_mode') === $mode)>{{ $mode }}</option>@endforeach</select>
            <select name="payment_status" class="rounded-md border-gray-300" required>@foreach(['Cleared','Pending','Bounced','Cancelled'] as $status)<option>{{ $status }}</option>@endforeach</select>
            <x-text-input name="transaction_ref" placeholder="Txn/UTR" />
            <x-text-input name="receipt_no" placeholder="Receipt no auto" />
            <x-text-input name="bank_name" placeholder="Bank" />
            <x-text-input name="cheque_no" placeholder="Cheque/DD no" />
            <x-text-input name="cheque_date" type="date" />
            <x-text-input name="remarks" placeholder="Remarks" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Collect Fee</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Receipt</th><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Amount</th><th class="px-4 py-3 text-left">Mode</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($payments as $payment)<tr><td class="px-4 py-3 font-semibold">{{ $payment->receipt_no }}</td><td class="px-4 py-3">{{ $payment->student?->enrollment_no }}</td><td class="px-4 py-3">{{ number_format($payment->amount_paid, 2) }}</td><td class="px-4 py-3">{{ $payment->payment_mode }}</td><td class="px-4 py-3">{{ $payment->payment_status }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('fees.collections.destroy', $payment) }}" onsubmit="return confirm('Delete payment?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No collections.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $payments->links() }}</div>
    </div>
</x-app-layout>
