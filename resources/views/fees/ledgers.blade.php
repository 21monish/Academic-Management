<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Student Fee Ledger</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('fees.ledgers.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="student_id" class="rounded-md border-gray-300" required><option value="">Student</option>@foreach($students as $student)<option value="{{ $student->student_id }}">{{ $student->enrollment_no }} - {{ $student->first_name }} {{ $student->last_name }}</option>@endforeach</select>
            <select name="fee_structure_id" class="rounded-md border-gray-300" required><option value="">Fee structure</option>@foreach($structuresList as $structure)<option value="{{ $structure->fee_structure_id }}">{{ $structure->feeCategory?->name }} - {{ number_format($structure->amount, 2) }}</option>@endforeach</select>
            <select name="academic_year_id" class="rounded-md border-gray-300" required><option value="">Academic year</option>@foreach($academicYears as $year)<option value="{{ $year->academic_year_id }}">{{ $year->label }}</option>@endforeach</select>
            <select name="semester_id" class="rounded-md border-gray-300" required><option value="">Semester</option>@foreach($semesters as $semester)<option value="{{ $semester->semester_id }}">Sem {{ $semester->semester_no }}</option>@endforeach</select>
            <x-text-input name="total_amount" type="number" step="0.01" placeholder="Total amount" required />
            <x-text-input name="concession_amount" type="number" step="0.01" placeholder="Concession" />
            <x-text-input name="scholarship_amount" type="number" step="0.01" placeholder="Scholarship" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Create Ledger</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Fee</th><th class="px-4 py-3 text-left">Net</th><th class="px-4 py-3 text-left">Paid</th><th class="px-4 py-3 text-left">Balance</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($ledgers as $ledger)<tr><td class="px-4 py-3">{{ $ledger->student?->enrollment_no }}</td><td class="px-4 py-3">{{ $ledger->feeStructure?->feeCategory?->name }}</td><td class="px-4 py-3">{{ number_format($ledger->net_payable, 2) }}</td><td class="px-4 py-3">{{ number_format($ledger->amount_paid, 2) }}</td><td class="px-4 py-3"><span class="font-semibold {{ $ledger->balance_due > 0 ? 'text-red-600' : 'text-emerald-700' }}">{{ number_format($ledger->balance_due, 2) }} / {{ $ledger->payment_status }}</span></td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('fees.ledgers.destroy', $ledger) }}" onsubmit="return confirm('Delete ledger?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No ledger records.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $ledgers->links() }}</div>
    </div>
</x-app-layout>
