<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Fee Concessions</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <form method="POST" action="{{ route('fees.concessions.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="student_id" class="rounded-md border-gray-300" required><option value="">Student</option>@foreach($students as $student)<option value="{{ $student->student_id }}">{{ $student->enrollment_no }} - {{ $student->first_name }}</option>@endforeach</select>
            <select name="ledger_id" class="rounded-md border-gray-300" required><option value="">Ledger</option>@foreach($ledgersList as $ledger)<option value="{{ $ledger->ledger_id }}">{{ $ledger->student?->enrollment_no }} / {{ number_format($ledger->balance_due, 2) }}</option>@endforeach</select>
            <select name="concession_type" class="rounded-md border-gray-300" required>@foreach(['Merit','Sports','Staff Ward','Physically Challenged'] as $type)<option>{{ $type }}</option>@endforeach</select>
            <x-text-input name="approved_on" type="date" />
            <x-text-input name="concession_amount" type="number" step="0.01" placeholder="Amount" />
            <x-text-input name="concession_pct" type="number" step="0.01" placeholder="Percent" />
            <x-text-input name="reason" placeholder="Reason" />
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Active</label>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save Concession</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Value</th><th class="px-4 py-3 text-left">Approved</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($concessions as $concession)<tr><td class="px-4 py-3">{{ $concession->student?->enrollment_no }}</td><td class="px-4 py-3">{{ $concession->concession_type }}</td><td class="px-4 py-3">{{ number_format($concession->concession_amount ?? 0, 2) }} / {{ $concession->concession_pct ?? 0 }}%</td><td class="px-4 py-3">{{ $concession->approved_on }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('fees.concessions.destroy', $concession) }}" onsubmit="return confirm('Delete concession?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No concessions.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $concessions->links() }}</div>
    </div>
</x-app-layout>
