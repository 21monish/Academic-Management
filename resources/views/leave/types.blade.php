<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Leave Types</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('leave.types.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <x-text-input name="code" placeholder="CL" required />
            <x-text-input name="name" placeholder="Casual Leave" required />
            <select name="applicable_to" class="rounded-md border-gray-300" required>@foreach(['Teaching','NonTeaching','Both'] as $value)<option>{{ $value }}</option>@endforeach</select>
            <x-text-input name="max_days_per_year" type="number" placeholder="Max/year" />
            <x-text-input name="max_consecutive_days" type="number" placeholder="Max consecutive" />
            <x-text-input name="max_carry_forward_days" type="number" placeholder="Carry forward max" />
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_paid" value="1" checked class="rounded border-gray-300"> Paid</label>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="carry_forward_allowed" value="1" class="rounded border-gray-300"> Carry forward</label>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="requires_document" value="1" class="rounded border-gray-300"> Document</label>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Active</label>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-2">Save Leave Type</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Applicable</th><th class="px-4 py-3 text-left">Limit</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($types as $type)<tr><td class="px-4 py-3 font-semibold">{{ $type->code }}</td><td class="px-4 py-3">{{ $type->name }}</td><td class="px-4 py-3">{{ $type->applicable_to }}</td><td class="px-4 py-3">{{ $type->max_days_per_year ?? '-' }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('leave.types.destroy', $type) }}" onsubmit="return confirm('Delete leave type?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No leave types.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $types->links() }}</div>
    </div>
</x-app-layout>
