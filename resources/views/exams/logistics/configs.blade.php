<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Hall Ticket Config</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        <form method="POST" action="{{ route('exams.logistics.configs.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="exam_id" class="rounded-md border-gray-300" required><option value="">Exam</option>@foreach($exams as $exam)<option value="{{ $exam->exam_id }}">{{ $exam->exam_name }}</option>@endforeach</select>
            <select name="college_id" class="rounded-md border-gray-300" required><option value="">College</option>@foreach($colleges as $college)<option value="{{ $college->college_id }}">{{ $college->name }}</option>@endforeach</select>
            <x-text-input name="issue_start_date" type="date" /><x-text-input name="issue_end_date" type="date" />
            <x-text-input name="min_attendance_pct" type="number" step="0.01" placeholder="Min attendance %" />
            <label class="inline-flex items-center"><input type="hidden" name="fees_clearance_required" value="0"><input type="checkbox" name="fees_clearance_required" value="1" class="rounded border-slate-300 text-cyan-700" checked><span class="ms-2 text-sm">Fees clearance</span></label>
            <label class="inline-flex items-center"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-cyan-700" checked><span class="ms-2 text-sm">Active</span></label>
            <x-text-input name="principal_signature_url" placeholder="Principal signature URL" />
            <x-text-input name="college_seal_url" placeholder="College seal URL" class="md:col-span-2" />
            <textarea name="instructions" rows="2" class="rounded-md border-gray-300 md:col-span-2" placeholder="Instructions"></textarea>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save Config</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">College</th><th class="px-4 py-3 text-left">Attendance</th><th class="px-4 py-3 text-left">Active</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($configs as $config)<tr><td class="px-4 py-3">{{ $config->exam?->exam_name }}</td><td class="px-4 py-3">{{ $config->college?->name }}</td><td class="px-4 py-3">{{ $config->min_attendance_pct ?? '-' }}%</td><td class="px-4 py-3">{{ $config->is_active ? 'Yes' : 'No' }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.logistics.configs.destroy', $config) }}" onsubmit="return confirm('Delete config?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No configs.</td></tr>@endforelse</tbody></table>
        </div>
        <div class="mt-4">{{ $configs->links() }}</div>
    </div>
</x-app-layout>
