<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Seating Arrangement</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('exams.logistics.seating.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="schedule_id" class="rounded-md border-gray-300" required><option value="">Theory schedule</option>@foreach($theorySchedules as $schedule)<option value="{{ $schedule->schedule_id }}">{{ $schedule->exam?->exam_name }} / {{ $schedule->subject?->code }}</option>@endforeach</select>
            <select name="room_id" class="rounded-md border-gray-300" required><option value="">Room</option>@foreach($roomsList as $room)<option value="{{ $room->room_id }}">{{ $room->room_no }}</option>@endforeach</select>
            <select name="student_id" class="rounded-md border-gray-300" required><option value="">Student</option>@foreach($students as $student)<option value="{{ $student->student_id }}">{{ $student->enrollment_no }} - {{ $student->first_name }}</option>@endforeach</select>
            <select name="hall_ticket_id" class="rounded-md border-gray-300"><option value="">Hall ticket</option>@foreach($ticketsList as $ticket)<option value="{{ $ticket->hall_ticket_id }}">{{ $ticket->hall_ticket_no }}</option>@endforeach</select>
            <x-text-input name="seat_no" type="number" placeholder="Seat no" /><x-text-input name="seat_label" placeholder="Seat label" />
            <select name="status" class="rounded-md border-gray-300"><option>Assigned</option><option>Present</option><option>Absent</option><option>Malpractice</option></select>
            <select name="invigilator_staff_id" class="rounded-md border-gray-300"><option value="">Invigilator</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Assign Seat</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">Room</th><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Seat</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($seating as $seat)<tr><td class="px-4 py-3">{{ $seat->schedule?->exam?->exam_name }} / {{ $seat->schedule?->subject?->code }}</td><td class="px-4 py-3">{{ $seat->room?->room_no }}</td><td class="px-4 py-3">{{ $seat->student?->enrollment_no }}</td><td class="px-4 py-3">{{ $seat->seat_label ?? $seat->seat_no }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.logistics.seating.destroy', $seat) }}" onsubmit="return confirm('Delete seat assignment?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No seating records.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $seating->links() }}</div>
    </div>
</x-app-layout>
