<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Exam Subjects</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('exams.subjects.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            @csrf
            <select name="exam_id" class="rounded-md border-gray-300" required><option value="">Exam</option>@foreach($exams as $exam)<option value="{{ $exam->exam_id }}">{{ $exam->exam_name }}</option>@endforeach</select>
            <select name="subject_id" class="rounded-md border-gray-300" required><option value="">Subject</option>@foreach($subjects as $subject)<option value="{{ $subject->subject_id }}">{{ $subject->code }} - {{ $subject->name }}</option>@endforeach</select>
            <x-text-input name="exam_date" type="date" /><x-text-input name="exam_time" type="time" />
            <x-text-input name="max_theory_marks" type="number" placeholder="Max theory" /><x-text-input name="passing_theory_marks" type="number" placeholder="Pass theory" />
            <x-text-input name="max_practical_marks" type="number" placeholder="Max practical" /><x-text-input name="passing_practical_marks" type="number" placeholder="Pass practical" />
            <x-text-input name="max_internal_marks" type="number" placeholder="Max internal" /><x-text-input name="passing_internal_marks" type="number" placeholder="Pass internal" />
            <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white md:col-span-2">Save Subject</button>
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-left">Marks</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($examSubjects as $item)<tr><td class="px-4 py-3">{{ $item->exam?->exam_name }}</td><td class="px-4 py-3">{{ $item->subject?->code }}</td><td class="px-4 py-3">{{ $item->exam_date }} {{ $item->exam_time }}</td><td class="px-4 py-3">{{ ($item->max_theory_marks ?? 0) + ($item->max_practical_marks ?? 0) + ($item->max_internal_marks ?? 0) }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('exams.subjects.destroy', $item) }}" onsubmit="return confirm('Delete exam subject?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No exam subjects.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $examSubjects->links() }}</div>
    </div>
</x-app-layout>
