<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Leave Cancellations</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <form method="POST" action="{{ route('leave.cancellations.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
            @csrf
            <select name="application_id" class="rounded-md border-gray-300" required><option value="">Application</option>@foreach($applicationsList as $application)<option value="{{ $application->application_id }}">{{ $application->staff?->first_name }} / {{ $application->leaveType?->code }}</option>@endforeach</select>
            <select name="cancelled_by" class="rounded-md border-gray-300" required><option value="">Cancelled by</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <select name="cancel_status" class="rounded-md border-gray-300" required>@foreach(['Requested','Approved','Rejected'] as $value)<option>{{ $value }}</option>@endforeach</select>
            <select name="approved_by" class="rounded-md border-gray-300"><option value="">Approved by</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <x-text-input name="reason" placeholder="Reason" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-5">Save Cancellation</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Application</th><th class="px-4 py-3 text-left">Cancelled By</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Approved By</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($cancellations as $cancellation)<tr><td class="px-4 py-3">{{ $cancellation->application?->staff?->first_name }} / {{ $cancellation->application?->status }}</td><td class="px-4 py-3">{{ $cancellation->cancelledBy?->first_name }}</td><td class="px-4 py-3">{{ $cancellation->cancel_status }}</td><td class="px-4 py-3">{{ $cancellation->approvedBy?->first_name ?? '-' }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('leave.cancellations.destroy', $cancellation) }}" onsubmit="return confirm('Delete cancellation?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No cancellations.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $cancellations->links() }}</div>
    </div>
</x-app-layout>
