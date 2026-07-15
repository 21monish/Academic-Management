<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Leave Applications</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <form method="POST" action="{{ route('leave.applications.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="staff_id" class="rounded-md border-gray-300" required><option value="">Applicant</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <select name="leave_type_id" class="rounded-md border-gray-300" required><option value="">Leave type</option>@foreach($leaveTypes as $type)<option value="{{ $type->leave_type_id }}">{{ $type->code }} - {{ $type->name }}</option>@endforeach</select>
            <select name="academic_year_id" class="rounded-md border-gray-300" required><option value="">Academic year</option>@foreach($academicYears as $year)<option value="{{ $year->academic_year_id }}">{{ $year->label }}</option>@endforeach</select>
            <select name="applied_to_staff_id" class="rounded-md border-gray-300"><option value="">Reporting authority</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <x-text-input name="from_date" type="date" required />
            <x-text-input name="to_date" type="date" required />
            <select name="half_day_type" class="rounded-md border-gray-300" required>@foreach(['None','Morning','Afternoon'] as $value)<option>{{ $value }}</option>@endforeach</select>
            <select name="status" class="rounded-md border-gray-300" required>@foreach(['Draft','Pending','Approved','Rejected','Cancelled'] as $value)<option>{{ $value }}</option>@endforeach</select>
            <x-text-input name="document_url" placeholder="Document URL" />
            <x-text-input name="reason" placeholder="Reason" />
            <x-text-input name="applicant_remarks" placeholder="Remarks" class="md:col-span-2" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save Application</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Staff</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Dates</th><th class="px-4 py-3 text-left">Days</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($applications as $application)<tr><td class="px-4 py-3">{{ $application->staff?->first_name }} {{ $application->staff?->last_name }}</td><td class="px-4 py-3">{{ $application->leaveType?->code }}</td><td class="px-4 py-3">{{ $application->from_date }} to {{ $application->to_date }}</td><td class="px-4 py-3">{{ $application->total_days }}</td><td class="px-4 py-3">{{ $application->status }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('leave.applications.destroy', $application) }}" onsubmit="return confirm('Delete application?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No leave applications.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $applications->links() }}</div>
    </div>
</x-app-layout>
