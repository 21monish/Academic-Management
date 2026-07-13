<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Practical Marks</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        <form method="POST" action="{{ route('exams.logistics.practical-marks.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="batch_id" class="rounded-md border-gray-300" required><option value="">Batch</option>@foreach($practicalBatchesList as $batch)<option value="{{ $batch->batch_id }}">{{ $batch->batch_name ?? 'Batch '.$batch->batch_no }} / {{ $batch->schedule?->subject?->code }}</option>@endforeach</select>
            <select name="student_id" class="rounded-md border-gray-300" required><option value="">Student</option>@foreach($students as $student)<option value="{{ $student->student_id }}">{{ $student->enrollment_no }} - {{ $student->first_name }}</option>@endforeach</select>
            <select name="subject_id" class="rounded-md border-gray-300" required><option value="">Subject</option>@foreach($subjects as $subject)<option value="{{ $subject->subject_id }}">{{ $subject->code }}</option>@endforeach</select>
            <select name="marked_by_staff_id" class="rounded-md border-gray-300"><option value="">Marked by</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <x-text-input name="journal_marks" type="number" step="0.01" placeholder="Journal" /><x-text-input name="viva_marks" type="number" step="0.01" placeholder="Viva" /><x-text-input name="performance_marks" type="number" step="0.01" placeholder="Performance" /><x-text-input name="max_marks" type="number" step="0.01" placeholder="Max" />
            <x-text-input name="grade" placeholder="Grade" /><select name="result_status" class="rounded-md border-gray-300"><option value="">Status</option><option>Pass</option><option>Fail</option></select><x-text-input name="remarks" placeholder="Remarks" class="md:col-span-2" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save Marks</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Marks</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($marks as $mark)<tr><td class="px-4 py-3">{{ $mark->student?->enrollment_no }}</td><td class="px-4 py-3">{{ $mark->subject?->code }}</td><td class="px-4 py-3">{{ $mark->total_marks }} / {{ $mark->max_marks }}</td><td class="px-4 py-3">{{ $mark->result_status }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.logistics.practical-marks.destroy', $mark) }}" onsubmit="return confirm('Delete marks?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No practical marks.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $marks->links() }}</div>
    </div>
</x-app-layout>
