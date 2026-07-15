<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Practical Exam Schedule</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('exams.logistics.practical-schedules.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="exam_id" class="rounded-md border-gray-300" required><option value="">Exam</option>@foreach($exams as $exam)<option value="{{ $exam->exam_id }}">{{ $exam->exam_name }}</option>@endforeach</select>
            <select name="subject_id" class="rounded-md border-gray-300" required><option value="">Subject</option>@foreach($subjects as $subject)<option value="{{ $subject->subject_id }}">{{ $subject->code }}</option>@endforeach</select>
            <select name="college_id" class="rounded-md border-gray-300" required><option value="">College</option>@foreach($colleges as $college)<option value="{{ $college->college_id }}">{{ $college->name }}</option>@endforeach</select>
            <select name="dept_id" class="rounded-md border-gray-300" required><option value="">Department</option>@foreach($departments as $dept)<option value="{{ $dept->dept_id }}">{{ $dept->name }}</option>@endforeach</select>
            <x-text-input name="exam_date" type="date" /><x-text-input name="start_time" type="time" /><x-text-input name="end_time" type="time" /><x-text-input name="lab_no" placeholder="Lab" />
            <x-text-input name="batch_size" type="number" placeholder="Batch size" /><select name="status" class="rounded-md border-gray-300"><option>Scheduled</option><option>Ongoing</option><option>Completed</option></select>
            <select name="internal_examiner_staff_id" class="rounded-md border-gray-300"><option value="">Internal examiner</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <label class="inline-flex items-center"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-cyan-700"><span class="ms-2 text-sm">Published</span></label>
            <x-text-input name="external_examiner_name" placeholder="External examiner" class="md:col-span-2" /><x-text-input name="external_examiner_org" placeholder="External organization" class="md:col-span-2" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save Schedule</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Lab</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($schedules as $schedule)<tr><td class="px-4 py-3">{{ $schedule->exam?->exam_name }}</td><td class="px-4 py-3">{{ $schedule->subject?->code }}</td><td class="px-4 py-3">{{ $schedule->exam_date }}</td><td class="px-4 py-3">{{ $schedule->lab_no }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.logistics.practical-schedules.destroy', $schedule) }}" onsubmit="return confirm('Delete practical schedule?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No practical schedules.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $schedules->links() }}</div>
    </div>
</x-app-layout>
