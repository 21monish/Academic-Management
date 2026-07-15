<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Lectures</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('attendance.lectures.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
            @csrf
            <select name="slot_id" class="rounded-md border-gray-300 md:col-span-2" required><option value="">Slot</option>@foreach($slots as $slot)<option value="{{ $slot->slot_id }}">{{ $slot->day_of_week }} {{ $slot->start_time }} / Sem {{ $slot->semester?->semester_no }} / {{ $slot->subject?->code }}</option>@endforeach</select>
            <x-text-input name="lecture_date" type="date" :value="date('Y-m-d')" required />
            <select name="lecture_type" class="rounded-md border-gray-300"><option value="">Slot Type</option><option>Theory</option><option>Lab</option></select>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Create</button>
            <textarea name="topic_covered" rows="2" class="rounded-md border-gray-300 md:col-span-5" placeholder="Topic covered"></textarea>
            <input type="hidden" name="is_extra" value="0"><input type="hidden" name="is_cancelled" value="0">
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Staff</th><th class="px-4 py-3 text-left">Topic</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($lectures as $lecture)<tr><td class="px-4 py-3">{{ $lecture->lecture_date }}</td><td class="px-4 py-3">{{ $lecture->subject?->code }}</td><td class="px-4 py-3">{{ $lecture->staff?->first_name }} {{ $lecture->staff?->last_name }}</td><td class="px-4 py-3">{{ $lecture->topic_covered }}</td><td class="px-4 py-3 text-right"><a href="{{ route('attendance.mark', $lecture) }}" class="font-semibold text-cyan-700">Mark</a><form method="POST" action="{{ route('attendance.lectures.destroy', $lecture) }}" class="inline" onsubmit="return confirm('Delete lecture?')">@csrf @method('DELETE')<button class="ms-3 font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No lectures.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $lectures->links() }}</div>
    </div>
</x-app-layout>
