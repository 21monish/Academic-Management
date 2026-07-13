<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Result Cards</h2></x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
            <select name="student_id" class="rounded-md border-gray-300"><option value="">All students</option>@foreach($students as $student)<option value="{{ $student->student_id }}" @selected((string) request('student_id') === (string) $student->student_id)>{{ $student->enrollment_no }} - {{ $student->first_name }}</option>@endforeach</select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            @if(request('student_id'))<a href="{{ route('reports.results.print', request('student_id')) }}" class="rounded-lg bg-cyan-700 px-4 py-2 text-center text-sm font-semibold text-white">Print Card</a>@endif
        </form>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Exam</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Marks</th><th class="px-4 py-3 text-left">Grade</th><th class="px-4 py-3 text-left">Status</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($results as $result)<tr><td class="px-4 py-3">{{ $result->student?->enrollment_no }}</td><td class="px-4 py-3">{{ $result->examSubject?->exam?->exam_name }}</td><td class="px-4 py-3">{{ $result->examSubject?->subject?->code }}</td><td class="px-4 py-3">{{ $result->total_marks }}</td><td class="px-4 py-3">{{ $result->grade }}</td><td class="px-4 py-3">{{ $result->result_status }}</td></tr>@empty<tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No result rows.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $results->links() }}</div>
    </div>
</x-app-layout>
