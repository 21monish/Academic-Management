<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSummary;
use App\Models\College;
use App\Models\Curriculum;
use App\Models\Lecture;
use App\Models\Semester;
use App\Models\Staff;
use App\Models\StaffSubjectAssignment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TimetableSlot;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceModuleController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function assignments(): View
    {
        $staffId = request()->integer('staff_id');

        return view('attendance.assignments', array_merge($this->lookups(), [
            'assignments' => $this->accessScope->applyToAssignments(
                StaffSubjectAssignment::with(['staff', 'subject', 'semester', 'college']),
                request()->user()
            )
                ->when($staffId, fn ($query) => $query->where('staff_id', $staffId))
                ->latest('assignment_id')
                ->paginate(15)
                ->withQueryString(),
        ]));
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => [
                'required',
                Rule::exists('staff', 'staff_id')->where(fn ($query) => $query->whereIn('staff_type', ['Teaching', 'Both'])),
            ],
            'subject_id' => ['required', 'exists:subjects,subject_id'],
            'semester_id' => ['required', 'exists:semesters,semester_id'],
            'college_id' => ['required', 'exists:colleges,college_id'],
            'lecture_type' => ['required', 'in:Theory,Lab,Both'],
            'academic_year' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        abort_unless($this->accessScope->applyToStaff(Staff::whereKey($data['staff_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToColleges(College::whereKey($data['college_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($data['subject_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['semester_id']), $request->user())->exists(), 403);

        $staff = Staff::findOrFail($data['staff_id']);
        $semester = Semester::with('programme')->findOrFail($data['semester_id']);
        $subject = Subject::findOrFail($data['subject_id']);

        abort_unless((int) $staff->college_id === (int) $data['college_id'], 422, 'Selected college must match the teaching staff member.');

        $semesterHasCurriculum = Curriculum::where('semester_id', $semester->semester_id)->exists();
        if ($semesterHasCurriculum) {
            abort_unless(
                Curriculum::where('semester_id', $semester->semester_id)
                    ->where('programme_id', $semester->programme_id)
                    ->where('subject_id', $subject->subject_id)
                    ->exists(),
                422,
                'Selected subject is not configured in the selected semester curriculum.'
            );
        } else {
            abort_unless(
                (int) $subject->dept_id === (int) $staff->dept_id
                    || (int) $subject->dept_id === (int) ($semester->programme?->dept_id ?? 0),
                422,
                'Selected subject must belong to the staff or semester department.'
            );
        }

        StaffSubjectAssignment::updateOrCreate(
            [
                'staff_id' => $data['staff_id'],
                'subject_id' => $data['subject_id'],
                'semester_id' => $data['semester_id'],
                'academic_year' => $data['academic_year'] ?? null,
            ],
            [
                'college_id' => $data['college_id'],
                'lecture_type' => $data['lecture_type'],
                'is_active' => $data['is_active'] ?? true,
            ]
        );

        return back()->with('status', 'Staff subject assignment saved.');
    }

    public function destroyAssignment(StaffSubjectAssignment $assignment): RedirectResponse
    {
        abort_unless($this->accessScope->applyToAssignments(StaffSubjectAssignment::whereKey($assignment->assignment_id), request()->user())->exists(), 403);

        $assignment->delete();

        return back()->with('status', 'Assignment deleted.');
    }

    public function slots(): View
    {
        return view('attendance.slots', array_merge($this->lookups(), [
            'slots' => $this->accessScope->applyToSlots(
                TimetableSlot::with(['college', 'semester', 'subject', 'staff']),
                request()->user()
            )
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->paginate(15),
        ]));
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'semester_id' => ['required', 'exists:semesters,semester_id'],
            'subject_id' => ['required', 'exists:subjects,subject_id'],
            'staff_id' => ['required', 'exists:staff,staff_id'],
            'day_of_week' => ['required', 'in:Mon,Tue,Wed,Thu,Fri,Sat'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'lecture_type' => ['required', 'in:Theory,Lab'],
            'room_no' => ['nullable', 'string', 'max:20'],
            'academic_year' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        abort_unless($this->accessScope->applyToStaff(Staff::whereKey($data['staff_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToColleges(College::whereKey($data['college_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($data['subject_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['semester_id']), $request->user())->exists(), 403);

        TimetableSlot::create($data);

        return back()->with('status', 'Timetable slot saved.');
    }

    public function destroySlot(TimetableSlot $slot): RedirectResponse
    {
        abort_unless($this->accessScope->applyToSlots(TimetableSlot::whereKey($slot->slot_id), request()->user())->exists(), 403);

        $slot->delete();

        return back()->with('status', 'Timetable slot deleted.');
    }

    public function lectures(): View
    {
        return view('attendance.lectures', array_merge($this->lookups(), [
            'slots' => $this->accessScope->applyToSlots(TimetableSlot::with(['semester', 'subject', 'staff']), request()->user())
                ->orderBy('day_of_week')
                ->get(),
            'lectures' => $this->accessScope->applyToLectures(
                Lecture::with(['slot.semester', 'staff', 'subject']),
                request()->user()
            )
                ->latest('lecture_date')
                ->paginate(15),
        ]));
    }

    public function storeLecture(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slot_id' => ['required', 'exists:timetable_slots,slot_id'],
            'lecture_date' => ['required', 'date'],
            'lecture_type' => ['nullable', 'in:Theory,Lab'],
            'topic_covered' => ['nullable', 'string'],
            'is_extra' => ['boolean'],
            'is_cancelled' => ['boolean'],
            'cancel_reason' => ['nullable', 'string'],
        ]);

        $slot = TimetableSlot::findOrFail($data['slot_id']);
        abort_unless($this->accessScope->applyToSlots(TimetableSlot::whereKey($slot->slot_id), $request->user())->exists(), 403);

        $data['staff_id'] = $slot->staff_id;
        $data['subject_id'] = $slot->subject_id;
        $data['lecture_type'] = $data['lecture_type'] ?: $slot->lecture_type;

        Lecture::create($data);

        return back()->with('status', 'Lecture created.');
    }

    public function destroyLecture(Lecture $lecture): RedirectResponse
    {
        abort_unless($this->accessScope->applyToLectures(Lecture::whereKey($lecture->lecture_id), request()->user())->exists(), 403);

        $lecture->delete();

        return back()->with('status', 'Lecture deleted.');
    }

    public function mark(Lecture $lecture): View
    {
        abort_unless($this->accessScope->applyToLectures(Lecture::whereKey($lecture->lecture_id), request()->user())->exists(), 403);

        $lecture->load(['slot.semester.programme', 'staff', 'subject', 'attendances']);
        $students = $this->studentsForLecture($lecture);
        $statusCounts = $lecture->attendances
            ->whereIn('student_id', $students->pluck('student_id'))
            ->countBy('status');

        return view('attendance.mark', compact('lecture', 'students', 'statusCounts'));
    }

    public function storeMark(Request $request, Lecture $lecture): RedirectResponse
    {
        abort_unless($this->accessScope->applyToLectures(Lecture::whereKey($lecture->lecture_id), $request->user())->exists(), 403);

        $lecture->load('slot.semester');
        $studentIds = $this->studentsForLecture($lecture)->pluck('student_id')->map(fn ($id) => (string) $id);

        $data = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*.status' => ['required', 'in:Present,Absent,Late,Excused'],
            'attendance.*.remarks' => ['nullable', 'string', 'max:200'],
        ]);

        abort_if($lecture->is_cancelled, 422, 'Attendance cannot be marked for a cancelled lecture.');
        abort_if($studentIds->isEmpty(), 422, 'No enrolled students found for this lecture.');
        abort_if(collect(array_keys($data['attendance']))->diff($studentIds)->isNotEmpty(), 422, 'Attendance contains students outside this lecture semester.');

        DB::transaction(function () use ($data, $lecture) {
            foreach ($data['attendance'] as $studentId => $row) {
                Attendance::updateOrCreate(
                    ['lecture_id' => $lecture->lecture_id, 'student_id' => $studentId],
                    [
                        'status' => $row['status'],
                        'remarks' => $row['remarks'] ?? null,
                        'marked_by' => $lecture->staff_id,
                        'marked_at' => now(),
                    ]
                );
            }

            $this->refreshSummaries($lecture->slot?->semester_id, $lecture->subject_id);
        });

        return redirect()->route('attendance.lectures')->with('status', 'Attendance marked.');
    }

    public function summaries(Request $request): View
    {
        $summaries = $this->accessScope->applyToAttendanceSummaries(
            AttendanceSummary::with(['student', 'subject', 'semester']),
            $request->user()
        )
            ->when($request->filled('semester_id'), fn ($query) => $query->where('semester_id', $request->integer('semester_id')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', $request->integer('subject_id')))
            ->orderBy('attendance_percentage')
            ->paginate(20)
            ->withQueryString();

        return view('attendance.summaries', array_merge($this->lookups(), compact('summaries')));
    }

    public function defaulters(Request $request): View
    {
        $threshold = (float) $request->input('threshold', 75);
        $summaries = $this->accessScope->applyToAttendanceSummaries(
            AttendanceSummary::with(['student', 'subject', 'semester']),
            $request->user()
        )
            ->where('attendance_percentage', '<', $threshold)
            ->orderBy('attendance_percentage')
            ->paginate(20)
            ->withQueryString();

        return view('attendance.defaulters', compact('summaries', 'threshold'));
    }

    private function refreshSummaries(?int $semesterId, int $subjectId): void
    {
        if (! $semesterId) {
            return;
        }

        $students = Student::whereHas('enrollments', fn ($query) => $query->where('semester_id', $semesterId))->get();

        foreach ($students as $student) {
            $total = Attendance::where('student_id', $student->student_id)
                ->whereHas('lecture', fn ($query) => $query->where('subject_id', $subjectId)->whereHas('slot', fn ($slot) => $slot->where('semester_id', $semesterId)))
                ->count();
            $attended = Attendance::where('student_id', $student->student_id)
                ->whereIn('status', ['Present', 'Late', 'Excused'])
                ->whereHas('lecture', fn ($query) => $query->where('subject_id', $subjectId)->whereHas('slot', fn ($slot) => $slot->where('semester_id', $semesterId)))
                ->count();

            AttendanceSummary::updateOrCreate(
                ['student_id' => $student->student_id, 'subject_id' => $subjectId, 'semester_id' => $semesterId],
                [
                    'total_lectures' => $total,
                    'attended_lectures' => $attended,
                    'attendance_percentage' => $total > 0 ? round(($attended / $total) * 100, 2) : null,
                ]
            );
        }
    }

    private function studentsForLecture(Lecture $lecture): Collection
    {
        return Student::where('programme_id', $lecture->slot?->semester?->programme_id)
            ->where('is_active', true)
            ->whereHas('enrollments', fn ($query) => $query->where('semester_id', $lecture->slot?->semester_id))
            ->orderBy('enrollment_no')
            ->get();
    }

    private function lookups(): array
    {
        return [
            'colleges' => $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']),
            'semesters' => $this->accessScope->applyToSemesters(Semester::with('programme'), request()->user())
                ->orderBy('semester_no')
                ->get(['semester_id', 'programme_id', 'semester_no', 'academic_year']),
            'subjects' => $this->accessScope->applyToSubjects(Subject::with('curriculum'), request()->user())
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['subject_id', 'dept_id', 'code', 'name', 'type']),
            'staffMembers' => $this->accessScope->applyToStaff(Staff::query(), request()->user())
                ->whereIn('staff_type', ['Teaching', 'Both'])
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get(['staff_id', 'college_id', 'dept_id', 'first_name', 'last_name', 'employee_code']),
        ];
    }
}
