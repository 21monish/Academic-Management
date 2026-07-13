<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Fee Categories</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('fees.categories.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <x-text-input name="name" placeholder="Tuition Fee" required />
            <select name="fee_type" class="rounded-md border-gray-300" required><option value="">Fee type</option>@foreach(['Academic','Exam','Hostel','Transport','Misc'] as $type)<option>{{ $type }}</option>@endforeach</select>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_mandatory" value="1" checked class="rounded border-gray-300"> Mandatory</label>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Active</label>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_refundable" value="1" class="rounded border-gray-300"> Refundable</label>
            <x-text-input name="description" placeholder="Description" class="md:col-span-2" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Save Category</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Rules</th><th></th></tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse($categories as $category)<tr><td class="px-4 py-3 font-semibold">{{ $category->name }}</td><td class="px-4 py-3">{{ $category->fee_type }}</td><td class="px-4 py-3">{{ $category->is_mandatory ? 'Mandatory' : 'Optional' }} / {{ $category->is_refundable ? 'Refundable' : 'Non-refundable' }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('fees.categories.destroy', $category) }}" onsubmit="return confirm('Delete category?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No fee categories.</td></tr>@endforelse</tbody>
            </table>
        </div>
        <div class="mt-4">{{ $categories->links() }}</div>
    </div>
</x-app-layout>
