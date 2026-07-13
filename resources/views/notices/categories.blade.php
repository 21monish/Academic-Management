<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Notice Categories</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('notices.categories.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <x-text-input name="name" placeholder="Academic" required />
            <x-text-input name="color_code" placeholder="#0891b2" />
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Active</label>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Save Category</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Category</th><th class="px-4 py-3 text-left">Color</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($categories as $category)<tr><td class="px-4 py-3 font-semibold">{{ $category->name }}</td><td class="px-4 py-3"><span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded border border-slate-200" style="background: {{ $category->color_code ?: '#e2e8f0' }}"></span>{{ $category->color_code }}</span></td><td class="px-4 py-3">{{ $category->is_active ? 'Active' : 'Inactive' }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('notices.categories.destroy', $category) }}" onsubmit="return confirm('Delete category?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No notice categories.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $categories->links() }}</div>
    </div>
</x-app-layout>
