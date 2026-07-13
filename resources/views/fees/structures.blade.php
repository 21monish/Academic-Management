<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Fee Structures</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('fees.structures.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="college_id" class="rounded-md border-gray-300" required><option value="">College</option>@foreach($colleges as $college)<option value="{{ $college->college_id }}">{{ $college->name }}</option>@endforeach</select>
            <select name="programme_id" class="rounded-md border-gray-300" required><option value="">Programme</option>@foreach($programmes as $programme)<option value="{{ $programme->programme_id }}">{{ $programme->name }}</option>@endforeach</select>
            <select name="academic_year_id" class="rounded-md border-gray-300" required><option value="">Academic year</option>@foreach($academicYears as $year)<option value="{{ $year->academic_year_id }}">{{ $year->label }}</option>@endforeach</select>
            <select name="semester_id" class="rounded-md border-gray-300" required><option value="">Semester</option>@foreach($semesters as $semester)<option value="{{ $semester->semester_id }}">Sem {{ $semester->semester_no }}</option>@endforeach</select>
            <select name="fee_category_id" class="rounded-md border-gray-300" required><option value="">Fee category</option>@foreach($feeCategories as $category)<option value="{{ $category->fee_category_id }}">{{ $category->name }}</option>@endforeach</select>
            <select name="student_category_id" class="rounded-md border-gray-300"><option value="">All student categories</option>@foreach($studentCategories as $category)<option value="{{ $category->category_id }}">{{ $category->name }}</option>@endforeach</select>
            <x-text-input name="amount" type="number" step="0.01" placeholder="Amount" required />
            <x-text-input name="late_fine_per_day" type="number" step="0.01" placeholder="Late fine/day" />
            <x-text-input name="due_date" type="date" />
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Active</label>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-2">Save Structure</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Category</th><th class="px-4 py-3 text-left">Programme</th><th class="px-4 py-3 text-left">Year/Sem</th><th class="px-4 py-3 text-left">Amount</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($structures as $structure)<tr><td class="px-4 py-3">{{ $structure->feeCategory?->name }}</td><td class="px-4 py-3">{{ $structure->programme?->name }}</td><td class="px-4 py-3">{{ $structure->academicYear?->label }} / Sem {{ $structure->semester?->semester_no }}</td><td class="px-4 py-3 font-semibold">{{ number_format($structure->amount, 2) }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('fees.structures.destroy', $structure) }}" onsubmit="return confirm('Delete structure?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No fee structures.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $structures->links() }}</div>
    </div>
</x-app-layout>
