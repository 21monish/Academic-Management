<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Practical Batches</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('exams.logistics.practical-batches.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="prac_schedule_id" class="rounded-md border-gray-300 md:col-span-2" required><option value="">Practical schedule</option>@foreach($practicalSchedulesList as $schedule)<option value="{{ $schedule->prac_schedule_id }}">{{ $schedule->exam?->exam_name }} / {{ $schedule->subject?->code }}</option>@endforeach</select>
            <x-text-input name="batch_name" placeholder="Batch A" /><x-text-input name="batch_no" type="number" placeholder="Batch no" />
            <x-text-input name="batch_date" type="date" /><x-text-input name="start_time" type="time" /><x-text-input name="end_time" type="time" /><x-text-input name="max_students" type="number" placeholder="Max students" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Create Batch</button>
        </form>

        <form method="POST" action="{{ route('exams.logistics.practical-batches.students.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-cyan-100 bg-cyan-50 p-4 md:grid-cols-5">
            @csrf
            <select name="batch_id" class="rounded-md border-gray-300" required><option value="">Batch</option>@foreach($practicalBatchesList as $batch)<option value="{{ $batch->batch_id }}">{{ $batch->batch_name ?? 'Batch '.$batch->batch_no }} / {{ $batch->schedule?->subject?->code }}</option>@endforeach</select>
            <select name="student_id" class="rounded-md border-gray-300" required><option value="">Student</option>@foreach($students as $student)<option value="{{ $student->student_id }}">{{ $student->enrollment_no }} - {{ $student->first_name }}</option>@endforeach</select>
            <select name="hall_ticket_id" class="rounded-md border-gray-300"><option value="">Hall ticket</option>@foreach($ticketsList as $ticket)<option value="{{ $ticket->hall_ticket_id }}">{{ $ticket->hall_ticket_no }}</option>@endforeach</select>
            <x-text-input name="seat_no" type="number" placeholder="Seat no" />
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add Student</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Batch</th><th class="px-4 py-3 text-left">Schedule</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Students</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($batches as $batch)<tr><td class="px-4 py-3">{{ $batch->batch_name ?? 'Batch '.$batch->batch_no }}</td><td class="px-4 py-3">{{ $batch->schedule?->exam?->exam_name }} / {{ $batch->schedule?->subject?->code }}</td><td class="px-4 py-3">{{ $batch->batch_date }}</td><td class="px-4 py-3">{{ $batch->students->count() }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.logistics.practical-batches.destroy', $batch) }}" onsubmit="return confirm('Delete batch?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No batches.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $batches->links() }}</div>
    </div>
</x-app-layout>
