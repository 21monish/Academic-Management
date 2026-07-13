<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Invigilator Duties</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        <form method="POST" action="{{ route('exams.logistics.invigilators.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="schedule_id" class="rounded-md border-gray-300" required><option value="">Theory schedule</option>@foreach($theorySchedules as $schedule)<option value="{{ $schedule->schedule_id }}">{{ $schedule->exam?->exam_name }} / {{ $schedule->subject?->code }}</option>@endforeach</select>
            <select name="room_id" class="rounded-md border-gray-300" required><option value="">Room</option>@foreach($roomsList as $room)<option value="{{ $room->room_id }}">{{ $room->room_no }}</option>@endforeach</select>
            <select name="staff_id" class="rounded-md border-gray-300" required><option value="">Staff</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <select name="duty_type" class="rounded-md border-gray-300"><option>Invigilator</option><option>Chief</option><option>FlyingSquad</option><option>Observer</option></select>
            <x-text-input name="duty_start_time" type="time" /><x-text-input name="duty_end_time" type="time" />
            <label class="inline-flex items-center"><input type="hidden" name="is_confirmed" value="0"><input type="checkbox" name="is_confirmed" value="1" class="rounded border-slate-300 text-cyan-700"><span class="ms-2 text-sm">Confirmed</span></label>
            <x-text-input name="remarks" placeholder="Remarks" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save Duty</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">Room</th><th class="px-4 py-3 text-left">Staff</th><th class="px-4 py-3 text-left">Type</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($duties as $duty)<tr><td class="px-4 py-3">{{ $duty->schedule?->exam?->exam_name }} / {{ $duty->schedule?->subject?->code }}</td><td class="px-4 py-3">{{ $duty->room?->room_no }}</td><td class="px-4 py-3">{{ $duty->staff?->first_name }} {{ $duty->staff?->last_name }}</td><td class="px-4 py-3">{{ $duty->duty_type }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.logistics.invigilators.destroy', $duty) }}" onsubmit="return confirm('Delete duty?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No duties.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $duties->links() }}</div>
    </div>
</x-app-layout>
