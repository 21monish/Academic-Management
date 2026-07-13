<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Exam Setup</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('partials._flash')
        <form method="POST" action="{{ route('exams.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="academic_year_id" class="rounded-md border-gray-300" required><option value="">Academic Year</option>@foreach($academicYears as $year)<option value="{{ $year->academic_year_id }}">{{ $year->label }}</option>@endforeach</select>
            <select name="semester_id" class="rounded-md border-gray-300" required><option value="">Semester</option>@foreach($semesters as $semester)<option value="{{ $semester->semester_id }}">Sem {{ $semester->semester_no }}</option>@endforeach</select>
            <select name="college_id" class="rounded-md border-gray-300" required><option value="">College</option>@foreach($colleges as $college)<option value="{{ $college->college_id }}">{{ $college->name }}</option>@endforeach</select>
            <select name="exam_type" class="rounded-md border-gray-300" required>@foreach(['MidSem','EndSem','Remedial','Backlog'] as $type)<option>{{ $type }}</option>@endforeach</select>
            <x-text-input name="exam_name" placeholder="Exam name" required /><x-text-input name="start_date" type="date" /><x-text-input name="end_date" type="date" />
            <label class="inline-flex items-center"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-cyan-700"><span class="ms-2 text-sm">Published</span></label>
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save Exam</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Semester</th><th class="px-4 py-3 text-left">Published</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($exams as $exam)<tr><td class="px-4 py-3">{{ $exam->exam_name }}</td><td class="px-4 py-3">{{ $exam->exam_type }}</td><td class="px-4 py-3">Sem {{ $exam->semester?->semester_no }}</td><td class="px-4 py-3">{{ $exam->is_published ? 'Yes' : 'No' }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.destroy', $exam) }}" onsubmit="return confirm('Delete exam?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No exams.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $exams->links() }}</div>
    </div>
</x-app-layout>
