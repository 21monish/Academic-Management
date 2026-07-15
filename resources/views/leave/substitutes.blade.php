<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Leave Substitutes</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <form method="POST" action="{{ route('leave.substitutes.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="application_id" class="rounded-md border-gray-300" required><option value="">Application</option>@foreach($applicationsList as $application)<option value="{{ $application->application_id }}">{{ $application->staff?->first_name }} / {{ $application->leaveType?->code }}</option>@endforeach</select>
            <select name="substitute_staff_id" class="rounded-md border-gray-300" required><option value="">Substitute staff</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <select name="subject_id" class="rounded-md border-gray-300" required><option value="">Subject</option>@foreach($subjects as $subject)<option value="{{ $subject->subject_id }}">{{ $subject->code }} - {{ $subject->name }}</option>@endforeach</select>
            <x-text-input name="class_date" type="date" />
            <x-text-input name="start_time" type="time" />
            <x-text-input name="end_time" type="time" />
            <select name="lecture_type" class="rounded-md border-gray-300"><option value="">Lecture type</option><option>Theory</option><option>Lab</option></select>
            <select name="status" class="rounded-md border-gray-300" required>@foreach(['Pending','Confirmed','Completed'] as $value)<option>{{ $value }}</option>@endforeach</select>
            <x-text-input name="remarks" placeholder="Remarks" class="md:col-span-3" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Save Substitute</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Applicant</th><th class="px-4 py-3 text-left">Substitute</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($substitutes as $substitute)<tr><td class="px-4 py-3">{{ $substitute->application?->staff?->first_name }}</td><td class="px-4 py-3">{{ $substitute->substituteStaff?->first_name }} {{ $substitute->substituteStaff?->last_name }}</td><td class="px-4 py-3">{{ $substitute->subject?->code }}</td><td class="px-4 py-3">{{ $substitute->class_date }}</td><td class="px-4 py-3">{{ $substitute->status }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('leave.substitutes.destroy', $substitute) }}" onsubmit="return confirm('Delete substitute?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No substitutes.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $substitutes->links() }}</div>
    </div>
</x-app-layout>
