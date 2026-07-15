<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Timetable Slots</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('attendance.slots.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="college_id" class="rounded-md border-gray-300" required><option value="">College</option>@foreach($colleges as $college)<option value="{{ $college->college_id }}">{{ $college->name }}</option>@endforeach</select>
            <select name="semester_id" class="rounded-md border-gray-300" required><option value="">Semester</option>@foreach($semesters as $semester)<option value="{{ $semester->semester_id }}">Sem {{ $semester->semester_no }}</option>@endforeach</select>
            <select name="subject_id" class="rounded-md border-gray-300" required><option value="">Subject</option>@foreach($subjects as $subject)<option value="{{ $subject->subject_id }}">{{ $subject->code }}</option>@endforeach</select>
            <select name="staff_id" class="rounded-md border-gray-300" required><option value="">Staff</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <select name="day_of_week" class="rounded-md border-gray-300" required>@foreach(['Mon','Tue','Wed','Thu','Fri','Sat'] as $day)<option>{{ $day }}</option>@endforeach</select>
            <x-text-input name="start_time" type="time" required /><x-text-input name="end_time" type="time" required />
            <select name="lecture_type" class="rounded-md border-gray-300" required><option>Theory</option><option>Lab</option></select>
            <x-text-input name="room_no" placeholder="Room" /><x-text-input name="academic_year" placeholder="2026-27" /><input type="hidden" name="is_active" value="1">
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-2">Save Slot</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Day</th><th class="px-4 py-3 text-left">Time</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Staff</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($slots as $slot)<tr><td class="px-4 py-3">{{ $slot->day_of_week }}</td><td class="px-4 py-3">{{ $slot->start_time }} - {{ $slot->end_time }}</td><td class="px-4 py-3">{{ $slot->subject?->code }}</td><td class="px-4 py-3">{{ $slot->staff?->first_name }} {{ $slot->staff?->last_name }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('attendance.slots.destroy', $slot) }}" onsubmit="return confirm('Delete slot?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No slots.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $slots->links() }}</div>
    </div>
</x-app-layout>
