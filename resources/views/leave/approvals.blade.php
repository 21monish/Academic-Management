<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Leave Approvals</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('leave.approvals.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
            @csrf
            <select name="application_id" class="rounded-md border-gray-300 md:col-span-2" required><option value="">Application</option>@foreach($applicationsList as $application)<option value="{{ $application->application_id }}">{{ $application->staff?->first_name }} / {{ $application->leaveType?->code }} / {{ $application->status }}</option>@endforeach</select>
            <select name="approver_staff_id" class="rounded-md border-gray-300" required><option value="">Approver</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <x-text-input name="approval_level" type="number" value="1" required />
            <select name="decision" class="rounded-md border-gray-300" required>@foreach(['Approved','Rejected','Forwarded'] as $value)<option>{{ $value }}</option>@endforeach</select>
            <x-text-input name="remarks" placeholder="Remarks" class="md:col-span-4" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Save Approval</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Application</th><th class="px-4 py-3 text-left">Approver</th><th class="px-4 py-3 text-left">Level</th><th class="px-4 py-3 text-left">Decision</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($approvals as $approval)<tr><td class="px-4 py-3">{{ $approval->application?->staff?->first_name }} / {{ $approval->application?->leaveType?->code }}</td><td class="px-4 py-3">{{ $approval->approver?->first_name }} {{ $approval->approver?->last_name }}</td><td class="px-4 py-3">{{ $approval->approval_level }}</td><td class="px-4 py-3">{{ $approval->decision }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('leave.approvals.destroy', $approval) }}" onsubmit="return confirm('Delete approval?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No approvals.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $approvals->links() }}</div>
    </div>
</x-app-layout>
