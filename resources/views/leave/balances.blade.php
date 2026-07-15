<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Leave Balances</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._table_filter')
        <form method="POST" action="{{ route('leave.balances.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
            @csrf
            <select name="staff_id" class="rounded-md border-gray-300" required><option value="">Staff</option>@foreach($staffMembers as $staff)<option value="{{ $staff->staff_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>@endforeach</select>
            <select name="leave_type_id" class="rounded-md border-gray-300" required><option value="">Leave type</option>@foreach($leaveTypes as $type)<option value="{{ $type->leave_type_id }}">{{ $type->code }} - {{ $type->name }}</option>@endforeach</select>
            <select name="academic_year_id" class="rounded-md border-gray-300" required><option value="">Academic year</option>@foreach($academicYears as $year)<option value="{{ $year->academic_year_id }}">{{ $year->label }}</option>@endforeach</select>
            <x-text-input name="total_allocated" type="number" step="0.5" placeholder="Allocated" />
            <x-text-input name="carry_forwarded" type="number" step="0.5" placeholder="Carry forward" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-5">Save Balance</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Staff</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Available</th><th class="px-4 py-3 text-left">Used</th><th class="px-4 py-3 text-left">Remaining</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($balances as $balance)<tr><td class="px-4 py-3">{{ $balance->staff?->first_name }} {{ $balance->staff?->last_name }}</td><td class="px-4 py-3">{{ $balance->leaveType?->code }}</td><td class="px-4 py-3">{{ $balance->total_available }}</td><td class="px-4 py-3">{{ $balance->used }} + {{ $balance->pending_approval }} pending</td><td class="px-4 py-3 font-semibold">{{ $balance->remaining }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('leave.balances.destroy', $balance) }}" onsubmit="return confirm('Delete balance?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No leave balances.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $balances->links() }}</div>
    </div>
</x-app-layout>
