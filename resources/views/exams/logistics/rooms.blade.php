<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Exam Rooms</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('exams.logistics.rooms.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="college_id" class="rounded-md border-gray-300" required><option value="">College</option>@foreach($colleges as $college)<option value="{{ $college->college_id }}">{{ $college->name }}</option>@endforeach</select>
            <x-text-input name="room_no" placeholder="Room No" required /><x-text-input name="building" placeholder="Building" /><x-text-input name="floor_no" type="number" placeholder="Floor" />
            <x-text-input name="seating_capacity" type="number" placeholder="Capacity" /><select name="room_type" class="rounded-md border-gray-300"><option value="">Room type</option><option>Hall</option><option>Classroom</option><option>Lab</option></select>
            <label class="inline-flex items-center"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-cyan-700" checked><span class="ms-2 text-sm">Active</span></label>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Save Room</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Room</th><th class="px-4 py-3 text-left">College</th><th class="px-4 py-3 text-left">Capacity</th><th class="px-4 py-3 text-left">Type</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($rooms as $room)<tr><td class="px-4 py-3">{{ $room->room_no }} {{ $room->building ? '/ '.$room->building : '' }}</td><td class="px-4 py-3">{{ $room->college?->name }}</td><td class="px-4 py-3">{{ $room->seating_capacity }}</td><td class="px-4 py-3">{{ $room->room_type }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.logistics.rooms.destroy', $room) }}" onsubmit="return confirm('Delete room?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No rooms.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $rooms->links() }}</div>
    </div>
</x-app-layout>
