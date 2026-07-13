<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Holiday Calendar</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        @include('partials._table_filter')
        <form method="POST" action="{{ route('leave.holidays.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
            @csrf
            <select name="college_id" class="rounded-md border-gray-300" required><option value="">College</option>@foreach($colleges as $college)<option value="{{ $college->college_id }}">{{ $college->name }}</option>@endforeach</select>
            <select name="academic_year_id" class="rounded-md border-gray-300" required><option value="">Academic year</option>@foreach($academicYears as $year)<option value="{{ $year->academic_year_id }}">{{ $year->label }}</option>@endforeach</select>
            <x-text-input name="holiday_name" placeholder="Holiday name" required />
            <x-text-input name="holiday_date" type="date" required />
            <select name="holiday_type" class="rounded-md border-gray-300"><option value="">Holiday type</option>@foreach(['National','State','Regional','College'] as $value)<option>{{ $value }}</option>@endforeach</select>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_optional" value="1" class="rounded border-gray-300"> Optional</label>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save Holiday</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Holiday</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">College</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($holidays as $holiday)<tr><td class="px-4 py-3 font-semibold">{{ $holiday->holiday_name }}</td><td class="px-4 py-3">{{ $holiday->holiday_date }}</td><td class="px-4 py-3">{{ $holiday->holiday_type }}{{ $holiday->is_optional ? ' / Optional' : '' }}</td><td class="px-4 py-3">{{ $holiday->college?->name }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('leave.holidays.destroy', $holiday) }}" onsubmit="return confirm('Delete holiday?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No holidays.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $holidays->links() }}</div>
    </div>
</x-app-layout>
