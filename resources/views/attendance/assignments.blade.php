<x-app-layout>
    @php
        $staffLookup = $staffMembers->mapWithKeys(fn ($staff) => [
            (string) $staff->staff_id => [
                'college_id' => (int) $staff->college_id,
                'dept_id' => (int) $staff->dept_id,
            ],
        ]);
        $semesterLookup = $semesters->mapWithKeys(fn ($semester) => [
            (string) $semester->semester_id => [
                'programme_id' => (int) $semester->programme_id,
                'dept_id' => (int) ($semester->programme?->dept_id ?? 0),
                'academic_year' => $semester->academic_year,
            ],
        ]);
        $subjectLookup = $subjects->mapWithKeys(fn ($subject) => [
            (string) $subject->subject_id => [
                'dept_id' => (int) $subject->dept_id,
                'curriculum' => $subject->curriculum
                    ->map(fn ($row) => [
                        'programme_id' => (int) $row->programme_id,
                        'semester_id' => (int) $row->semester_id,
                    ])
                    ->values(),
            ],
        ]);
    @endphp

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Teaching Staff Subject Assignments</h2>
                <p class="mt-1 text-sm text-slate-500">Assign subjects, semesters, lecture type, and academic year to teaching staff.</p>
            </div>
            <a href="{{ route('staff.index', ['staff_type' => 'Teaching']) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Teaching Staff</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if(hasPermission('staff_assignment.create') || hasPermission('staff_assignment.update'))
            <form method="POST" action="{{ route('attendance.assignments.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-7" data-assignment-form>
                @csrf
                <select name="staff_id" class="rounded-md border-gray-300" required data-staff-select>
                    <option value="">Teaching Staff</option>
                    @foreach($staffMembers as $staff)
                        <option value="{{ $staff->staff_id }}" data-college-id="{{ $staff->college_id }}" data-dept-id="{{ $staff->dept_id }}" @selected((string) old('staff_id', request('staff_id')) === (string) $staff->staff_id)>
                            {{ $staff->employee_code }} - {{ $staff->first_name }} {{ $staff->last_name }}
                        </option>
                    @endforeach
                </select>
                <select name="subject_id" class="rounded-md border-gray-300" required data-subject-select>
                    <option value="">Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->subject_id }}" data-dept-id="{{ $subject->dept_id }}" @selected((string) old('subject_id') === (string) $subject->subject_id)>{{ $subject->code }} - {{ $subject->name }}</option>
                    @endforeach
                </select>
                <select name="semester_id" class="rounded-md border-gray-300" required data-semester-select>
                    <option value="">Semester</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->semester_id }}" data-programme-id="{{ $semester->programme_id }}" data-dept-id="{{ $semester->programme?->dept_id }}" data-academic-year="{{ $semester->academic_year }}" @selected((string) old('semester_id') === (string) $semester->semester_id)>Sem {{ $semester->semester_no }}{{ $semester->programme?->name ? ' - '.$semester->programme->name : '' }}</option>
                    @endforeach
                </select>
                <select name="college_id" class="rounded-md border-gray-300 bg-slate-50" required data-college-select>
                    <option value="">College</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->college_id }}" @selected((string) old('college_id') === (string) $college->college_id)>{{ $college->name }}</option>
                    @endforeach
                </select>
                <select name="lecture_type" class="rounded-md border-gray-300" required>
                    @foreach(['Theory', 'Lab', 'Both'] as $type)
                        <option value="{{ $type }}" @selected(old('lecture_type', 'Theory') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <x-text-input name="academic_year" value="{{ old('academic_year') }}" placeholder="2026-27" data-academic-year-input />
                <button class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-semibold text-white">Assign</button>
                <input type="hidden" name="is_active" value="1">
                <p class="md:col-span-7 text-xs font-semibold text-slate-500" data-assignment-hint>Choose staff and semester to see matching subjects.</p>
            </form>
        @endif

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Staff</th><th class="px-4 py-3 text-left">Subject</th><th class="px-4 py-3 text-left">Semester</th><th class="px-4 py-3 text-left">College</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Year</th><th class="px-4 py-3 text-left">Status</th>@if(hasPermission('staff_assignment.delete') || hasPermission('staff_assignment.update'))<th></th>@endif</tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse($assignments as $assignment)<tr><td class="px-4 py-3"><div class="font-semibold text-slate-900">{{ $assignment->staff?->first_name }} {{ $assignment->staff?->last_name }}</div><div class="text-xs text-slate-500">{{ $assignment->staff?->employee_code }}</div></td><td class="px-4 py-3">{{ $assignment->subject?->code }} - {{ $assignment->subject?->name }}</td><td class="px-4 py-3">Sem {{ $assignment->semester?->semester_no }}</td><td class="px-4 py-3">{{ $assignment->college?->name }}</td><td class="px-4 py-3">{{ $assignment->lecture_type }}</td><td class="px-4 py-3">{{ $assignment->academic_year ?: '-' }}</td><td class="px-4 py-3">{{ $assignment->is_active ? 'Active' : 'Inactive' }}</td>@if(hasPermission('staff_assignment.delete') || hasPermission('staff_assignment.update'))<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('attendance.assignments.destroy', $assignment) }}" onsubmit="return confirm('Delete assignment?')">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></td>@endif</tr>@empty<tr><td colspan="{{ (hasPermission('staff_assignment.delete') || hasPermission('staff_assignment.update')) ? 8 : 7 }}" class="px-4 py-6 text-center text-slate-500">No assignments.</td></tr>@endforelse</tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assignments->links() }}</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-assignment-form]');
            if (! form) return;

            const staffSelect = form.querySelector('[data-staff-select]');
            const semesterSelect = form.querySelector('[data-semester-select]');
            const subjectSelect = form.querySelector('[data-subject-select]');
            const collegeSelect = form.querySelector('[data-college-select]');
            const academicYearInput = form.querySelector('[data-academic-year-input]');
            const hint = form.querySelector('[data-assignment-hint]');
            const staffLookup = @json($staffLookup);
            const semesterLookup = @json($semesterLookup);
            const subjectLookup = @json($subjectLookup);

            const hasCurriculumForSemester = (semesterId) => Object.values(subjectLookup).some((subject) => {
                return (subject.curriculum || []).some((row) => String(row.semester_id) === String(semesterId));
            });

            const subjectMatches = (subject, staff, semester, semesterId) => {
                if (! subject) return false;

                const semesterHasCurriculum = hasCurriculumForSemester(semesterId);
                if (semesterHasCurriculum) {
                    return (subject.curriculum || []).some((row) => {
                        return String(row.semester_id) === String(semesterId)
                            && String(row.programme_id) === String(semester?.programme_id || '');
                    });
                }

                if (! staff && ! semester) return true;

                return String(subject.dept_id) === String(staff?.dept_id || '')
                    || String(subject.dept_id) === String(semester?.dept_id || '');
            };

            const refreshAssignmentForm = () => {
                const staff = staffLookup[staffSelect.value] || null;
                const semester = semesterLookup[semesterSelect.value] || null;
                let visibleSubjects = 0;

                if (staff?.college_id) {
                    collegeSelect.value = String(staff.college_id);
                }

                if (semester?.academic_year && ! academicYearInput.value) {
                    academicYearInput.value = semester.academic_year;
                }

                Array.from(subjectSelect.options).forEach((option) => {
                    if (! option.value) return;

                    const subject = subjectLookup[option.value] || null;
                    const visible = ! semesterSelect.value || subjectMatches(subject, staff, semester, semesterSelect.value);
                    option.hidden = ! visible;
                    option.disabled = ! visible;
                    if (visible) visibleSubjects += 1;
                });

                if (subjectSelect.selectedOptions[0]?.disabled) {
                    subjectSelect.value = '';
                }

                if (hint) {
                    hint.textContent = semesterSelect.value
                        ? `${visibleSubjects} matching subject${visibleSubjects === 1 ? '' : 's'} available for the selected semester.`
                        : 'Choose staff and semester to see matching subjects.';
                }
            };

            staffSelect.addEventListener('change', refreshAssignmentForm);
            semesterSelect.addEventListener('change', refreshAssignmentForm);
            refreshAssignmentForm();
        });
    </script>
</x-app-layout>
