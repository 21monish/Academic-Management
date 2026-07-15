<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Mark Attendance</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $lecture->subject?->code }} - {{ $lecture->subject?->name }}
                </p>
            </div>
            <a href="{{ route('attendance.lectures') }}" class="inline-flex items-center justify-center rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                Back
            </a>
        </div>
    </x-slot>

    @php
        $statuses = [
            'Present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'Absent' => 'bg-red-50 text-red-700 ring-red-200',
            'Late' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'Excused' => 'bg-sky-50 text-sky-700 ring-sky-200',
        ];
        $markedCount = $statusCounts->sum();
        $totalStudents = $students->count();
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm lg:col-span-2">
                <div class="text-xs font-semibold uppercase text-slate-500">Lecture</div>
                <div class="mt-2 text-lg font-semibold text-slate-900">{{ $lecture->subject?->code }} - {{ $lecture->subject?->name }}</div>
                <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-600 sm:grid-cols-2">
                    <div><span class="font-semibold text-slate-800">Date:</span> {{ $lecture->lecture_date }}</div>
                    <div><span class="font-semibold text-slate-800">Type:</span> {{ $lecture->lecture_type ?? '-' }}</div>
                    <div><span class="font-semibold text-slate-800">Semester:</span> Sem {{ $lecture->slot?->semester?->semester_no ?? '-' }}</div>
                    <div><span class="font-semibold text-slate-800">Staff:</span> {{ $lecture->staff?->first_name }} {{ $lecture->staff?->last_name }}</div>
                    <div class="sm:col-span-2"><span class="font-semibold text-slate-800">Topic:</span> {{ $lecture->topic_covered ?: '-' }}</div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase text-slate-500">Progress</div>
                <div class="mt-2 text-3xl font-bold text-slate-900">{{ $markedCount }}/{{ $totalStudents }}</div>
                <div class="mt-1 text-sm text-slate-500">students already saved</div>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-cyan-600" style="width: {{ $totalStudents ? min(100, round(($markedCount / $totalStudents) * 100)) : 0 }}%"></div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-semibold uppercase text-slate-500">Saved Status</div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach($statuses as $status => $classes)
                        <div class="rounded-md px-3 py-2 ring-1 {{ $classes }}">
                            <div class="text-xs font-semibold">{{ $status }}</div>
                            <div class="text-lg font-bold">{{ $statusCounts->get($status, 0) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if($lecture->is_cancelled)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                This lecture is cancelled{{ $lecture->cancel_reason ? ': '.$lecture->cancel_reason : '.' }} Attendance cannot be marked.
            </div>
        @elseif($students->isEmpty())
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                <div class="text-lg font-semibold text-slate-900">No students found</div>
                <p class="mt-2 text-sm text-slate-500">Students must be enrolled in this lecture semester before attendance can be marked.</p>
            </div>
        @else
            <form method="POST" action="{{ route('attendance.mark.store', $lecture) }}" class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm" data-attendance-form>
                @csrf

                <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-sm font-semibold text-slate-800">
                        {{ $totalStudents }} enrolled students
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach(array_keys($statuses) as $status)
                            <button type="button" data-set-status="{{ $status }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                Mark all {{ $status }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Student</th>
                                <th class="w-48 px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $student)
                                @php($existing = $lecture->attendances->firstWhere('student_id', $student->student_id))
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900">{{ $student->enrollment_no }}</div>
                                        <div class="text-slate-600">{{ $student->first_name }} {{ $student->last_name }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="attendance[{{ $student->student_id }}][status]" class="w-full rounded-md border-gray-300 text-sm shadow-sm attendance-status">
                                            @foreach(array_keys($statuses) as $status)
                                                <option value="{{ $status }}" @selected(($existing?->status ?? 'Present') === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-text-input name="attendance[{{ $student->student_id }}][remarks]" class="w-full" :value="$existing?->remarks" maxlength="200" placeholder="Optional" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-slate-500">
                        Present, Late, and Excused count as attended in summaries.
                    </div>
                    <button class="rounded-lg bg-cyan-700 px-5 py-2 text-sm font-semibold text-white hover:bg-cyan-800">
                        Save Attendance
                    </button>
                </div>
            </form>
        @endif
    </div>

    <script>
        document.querySelectorAll('[data-set-status]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('[data-attendance-form] .attendance-status').forEach((select) => {
                    select.value = button.dataset.setStatus;
                });
            });
        });
    </script>
</x-app-layout>
