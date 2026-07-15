<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Scholarships</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <form method="POST" action="{{ route('fees.scholarships.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="student_id" class="rounded-md border-gray-300" required><option value="">Student</option>@foreach($students as $student)<option value="{{ $student->student_id }}">{{ $student->enrollment_no }} - {{ $student->first_name }}</option>@endforeach</select>
            <select name="academic_year_id" class="rounded-md border-gray-300" required><option value="">Academic year</option>@foreach($academicYears as $year)<option value="{{ $year->academic_year_id }}">{{ $year->label }}</option>@endforeach</select>
            <x-text-input name="scheme_name" placeholder="Scheme name" required />
            <x-text-input name="provider" placeholder="Provider" />
            <x-text-input name="amount" type="number" step="0.01" placeholder="Amount" />
            <select name="status" class="rounded-md border-gray-300" required>@foreach(['Applied','Approved','Disbursed','Rejected'] as $status)<option>{{ $status }}</option>@endforeach</select>
            <x-text-input name="applied_on" type="date" />
            <x-text-input name="approved_on" type="date" />
            <x-text-input name="reference_no" placeholder="Reference no" />
            <x-text-input name="remarks" placeholder="Remarks" class="md:col-span-2" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Save Scholarship</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Scheme</th><th class="px-4 py-3 text-left">Amount</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($scholarships as $scholarship)<tr><td class="px-4 py-3">{{ $scholarship->student?->enrollment_no }}</td><td class="px-4 py-3">{{ $scholarship->scheme_name }}</td><td class="px-4 py-3">{{ number_format($scholarship->amount ?? 0, 2) }}</td><td class="px-4 py-3">{{ $scholarship->status }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('fees.scholarships.destroy', $scholarship) }}" onsubmit="return confirm('Delete scholarship?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No scholarships.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $scholarships->links() }}</div>
    </div>
</x-app-layout>
