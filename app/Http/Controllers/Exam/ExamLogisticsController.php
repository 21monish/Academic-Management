<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSummary;
use App\Models\College;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamRoom;
use App\Models\HallTicket;
use App\Models\HallTicketConfig;
use App\Models\InvigilatorDuty;
use App\Models\PracticalBatch;
use App\Models\PracticalBatchStudent;
use App\Models\PracticalExamSchedule;
use App\Models\PracticalMarks;
use App\Models\SeatingArrangement;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\TheoryExamSchedule;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamLogisticsController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function configs(): View
    {
        return view('exams.logistics.configs', array_merge($this->lookups(), [
            'configs' => HallTicketConfig::with(['exam', 'college'])->latest('config_id')->paginate(15),
        ]));
    }

    public function storeConfig(Request $request): RedirectResponse
    {
        HallTicketConfig::create($request->validate([
            'exam_id' => ['required', 'exists:exams,exam_id'],
            'college_id' => ['required', 'exists:colleges,college_id'],
            'issue_start_date' => ['nullable', 'date'],
            'issue_end_date' => ['nullable', 'date', 'after_or_equal:issue_start_date'],
            'min_attendance_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fees_clearance_required' => ['boolean'],
            'is_active' => ['boolean'],
            'instructions' => ['nullable', 'string'],
            'principal_signature_url' => ['nullable', 'string', 'max:300'],
            'college_seal_url' => ['nullable', 'string', 'max:300'],
        ]));

        return back()->with('status', 'Hall ticket configuration saved.');
    }

    public function destroyConfig(HallTicketConfig $config): RedirectResponse
    {
        $config->delete();

        return back()->with('status', 'Hall ticket configuration deleted.');
    }

    public function tickets(): View
    {
        return view('exams.logistics.tickets', array_merge($this->lookups(), [
            'tickets' => HallTicket::with(['config.exam', 'student', 'enrollment.semester', 'subjects.subject'])
                ->latest('hall_ticket_id')
                ->paginate(20),
        ]));
    }

    public function generateTickets(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'config_id' => ['required', 'exists:hall_ticket_configs,config_id'],
            'exam_type' => ['nullable', 'in:Theory,Practical,Both'],
        ]);

        $config = HallTicketConfig::with('exam.examSubjects.subject')->findOrFail($data['config_id']);
        $exam = $config->exam;
        $enrollments = StudentEnrollment::with('student')
            ->where('semester_id', $exam->semester_id)
            ->get();
        $generated = 0;

        DB::transaction(function () use ($config, $exam, $enrollments, $data, &$generated) {
            foreach ($enrollments as $enrollment) {
                $avgAttendance = AttendanceSummary::where('student_id', $enrollment->student_id)->avg('attendance_percentage');
                $attendanceCleared = $avgAttendance === null || $avgAttendance >= ($config->min_attendance_pct ?? 0);
                $feesCleared = true;
                $eligible = $attendanceCleared && (! $config->fees_clearance_required || $feesCleared);
                $reason = $eligible ? null : trim(($attendanceCleared ? '' : 'Attendance below threshold. ') . ($feesCleared ? '' : 'Fees not cleared.'));

                $ticket = HallTicket::updateOrCreate(
                    ['config_id' => $config->config_id, 'student_id' => $enrollment->student_id, 'enrollment_id' => $enrollment->enrollment_id],
                    [
                        'hall_ticket_no' => 'HT-' . $exam->exam_id . '-' . $enrollment->student?->enrollment_no,
                        'exam_type' => $data['exam_type'] ?: 'Both',
                        'status' => $eligible ? 'Generated' : 'Draft',
                        'is_eligible' => $eligible,
                        'ineligibility_reason' => $reason,
                        'attendance_cleared' => $attendanceCleared,
                        'fees_cleared' => $feesCleared,
                        'generated' => $eligible,
                        'generated_at' => $eligible ? now() : null,
                        'generated_by' => auth()->id(),
                        'qr_code_data' => json_encode(['exam_id' => $exam->exam_id, 'student_id' => $enrollment->student_id]),
                        'barcode' => 'HT' . str_pad((string) $exam->exam_id, 4, '0', STR_PAD_LEFT) . str_pad((string) $enrollment->student_id, 6, '0', STR_PAD_LEFT),
                    ]
                );

                foreach ($exam->examSubjects as $examSubject) {
                    $ticket->subjects()->updateOrCreate(
                        ['subject_id' => $examSubject->subject_id],
                        [
                            'subject_type' => $data['exam_type'] ?: 'Both',
                            'theory_exam_date' => $examSubject->exam_date,
                            'theory_exam_time' => $examSubject->exam_time,
                            'is_eligible' => $eligible,
                            'ineligibility_reason' => $reason,
                        ]
                    );
                }

                $generated++;
            }
        });

        return back()->with('status', "{$generated} hall ticket record(s) processed.");
    }

    public function rooms(): View
    {
        return view('exams.logistics.rooms', array_merge($this->lookups(), [
            'rooms' => ExamRoom::with('college')->orderBy('room_no')->paginate(15),
        ]));
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        ExamRoom::create($request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'room_no' => ['required', 'string', 'max:20'],
            'building' => ['nullable', 'string', 'max:100'],
            'floor_no' => ['nullable', 'integer'],
            'seating_capacity' => ['nullable', 'integer', 'min:1'],
            'room_type' => ['nullable', 'in:Hall,Classroom,Lab'],
            'is_active' => ['boolean'],
        ]));

        return back()->with('status', 'Exam room saved.');
    }

    public function destroyRoom(ExamRoom $room): RedirectResponse
    {
        $room->delete();

        return back()->with('status', 'Exam room deleted.');
    }

    public function seating(): View
    {
        return view('exams.logistics.seating', array_merge($this->lookups(), [
            'seating' => SeatingArrangement::with(['schedule.exam', 'schedule.subject', 'room', 'student', 'hallTicket', 'invigilator'])
                ->latest('seating_id')
                ->paginate(20),
        ]));
    }

    public function storeSeating(Request $request): RedirectResponse
    {
        SeatingArrangement::create($request->validate([
            'schedule_id' => ['required', 'exists:theory_exam_schedules,schedule_id'],
            'room_id' => ['required', 'exists:exam_rooms,room_id'],
            'student_id' => ['required', 'exists:students,student_id'],
            'hall_ticket_id' => ['nullable', 'exists:hall_tickets,hall_ticket_id'],
            'seat_no' => ['nullable', 'integer', 'min:1'],
            'seat_label' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:Assigned,Present,Absent,Malpractice'],
            'is_present' => ['nullable', 'boolean'],
            'invigilator_staff_id' => ['nullable', 'exists:staff,staff_id'],
        ]));

        return back()->with('status', 'Seating assignment saved.');
    }

    public function destroySeating(SeatingArrangement $seating): RedirectResponse
    {
        $seating->delete();

        return back()->with('status', 'Seating assignment deleted.');
    }

    public function invigilators(): View
    {
        return view('exams.logistics.invigilators', array_merge($this->lookups(), [
            'duties' => InvigilatorDuty::with(['schedule.exam', 'schedule.subject', 'room', 'staff'])
                ->latest('duty_id')
                ->paginate(20),
        ]));
    }

    public function storeInvigilator(Request $request): RedirectResponse
    {
        InvigilatorDuty::create($request->validate([
            'schedule_id' => ['required', 'exists:theory_exam_schedules,schedule_id'],
            'room_id' => ['required', 'exists:exam_rooms,room_id'],
            'staff_id' => ['required', 'exists:staff,staff_id'],
            'duty_type' => ['required', 'in:Chief,Invigilator,FlyingSquad,Observer'],
            'duty_start_time' => ['nullable', 'date_format:H:i'],
            'duty_end_time' => ['nullable', 'date_format:H:i'],
            'is_confirmed' => ['boolean'],
            'remarks' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Invigilator duty saved.');
    }

    public function destroyInvigilator(InvigilatorDuty $duty): RedirectResponse
    {
        $duty->delete();

        return back()->with('status', 'Invigilator duty deleted.');
    }

    public function practicalSchedules(): View
    {
        return view('exams.logistics.practical-schedules', array_merge($this->lookups(), [
            'schedules' => PracticalExamSchedule::with(['exam', 'subject', 'college', 'department', 'internalExaminer'])
                ->latest('prac_schedule_id')
                ->paginate(20),
        ]));
    }

    public function storePracticalSchedule(Request $request): RedirectResponse
    {
        PracticalExamSchedule::create($request->validate([
            'exam_id' => ['required', 'exists:exams,exam_id'],
            'subject_id' => ['required', 'exists:subjects,subject_id'],
            'college_id' => ['required', 'exists:colleges,college_id'],
            'dept_id' => ['required', 'exists:departments,dept_id'],
            'exam_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'lab_no' => ['nullable', 'string', 'max:30'],
            'batch_size' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:Scheduled,Ongoing,Completed'],
            'external_examiner_name' => ['nullable', 'string', 'max:150'],
            'external_examiner_org' => ['nullable', 'string', 'max:200'],
            'internal_examiner_staff_id' => ['nullable', 'exists:staff,staff_id'],
            'is_published' => ['boolean'],
        ]));

        return back()->with('status', 'Practical schedule saved.');
    }

    public function destroyPracticalSchedule(PracticalExamSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return back()->with('status', 'Practical schedule deleted.');
    }

    public function practicalBatches(): View
    {
        return view('exams.logistics.practical-batches', array_merge($this->lookups(), [
            'batches' => PracticalBatch::with(['schedule.exam', 'schedule.subject', 'students.student'])
                ->latest('batch_id')
                ->paginate(20),
        ]));
    }

    public function storePracticalBatch(Request $request): RedirectResponse
    {
        PracticalBatch::create($request->validate([
            'prac_schedule_id' => ['required', 'exists:practical_exam_schedules,prac_schedule_id'],
            'batch_name' => ['nullable', 'string', 'max:50'],
            'batch_no' => ['nullable', 'integer'],
            'batch_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'max_students' => ['nullable', 'integer', 'min:1'],
        ]));

        return back()->with('status', 'Practical batch saved.');
    }

    public function storePracticalBatchStudent(Request $request): RedirectResponse
    {
        PracticalBatchStudent::create($request->validate([
            'batch_id' => ['required', 'exists:practical_batches,batch_id'],
            'student_id' => ['required', 'exists:students,student_id'],
            'hall_ticket_id' => ['nullable', 'exists:hall_tickets,hall_ticket_id'],
            'seat_no' => ['nullable', 'integer', 'min:1'],
            'attendance_status' => ['nullable', 'in:Present,Absent'],
        ]));

        return back()->with('status', 'Student added to practical batch.');
    }

    public function destroyPracticalBatch(PracticalBatch $batch): RedirectResponse
    {
        $batch->delete();

        return back()->with('status', 'Practical batch deleted.');
    }

    public function practicalMarks(): View
    {
        return view('exams.logistics.practical-marks', array_merge($this->lookups(), [
            'marks' => PracticalMarks::with(['batch.schedule.subject', 'student', 'subject', 'markedBy'])
                ->latest('prac_marks_id')
                ->paginate(20),
        ]));
    }

    public function storePracticalMarks(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'batch_id' => ['required', 'exists:practical_batches,batch_id'],
            'student_id' => ['required', 'exists:students,student_id'],
            'subject_id' => ['required', 'exists:subjects,subject_id'],
            'journal_marks' => ['nullable', 'numeric', 'min:0'],
            'viva_marks' => ['nullable', 'numeric', 'min:0'],
            'performance_marks' => ['nullable', 'numeric', 'min:0'],
            'max_marks' => ['nullable', 'numeric', 'min:0'],
            'grade' => ['nullable', 'string', 'max:5'],
            'result_status' => ['nullable', 'in:Pass,Fail'],
            'marked_by_staff_id' => ['nullable', 'exists:staff,staff_id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data['total_marks'] = ($data['journal_marks'] ?? 0) + ($data['viva_marks'] ?? 0) + ($data['performance_marks'] ?? 0);
        PracticalMarks::updateOrCreate(
            ['batch_id' => $data['batch_id'], 'student_id' => $data['student_id'], 'subject_id' => $data['subject_id']],
            $data + ['marked_at' => now()]
        );

        return back()->with('status', 'Practical marks saved.');
    }

    public function destroyPracticalMarks(PracticalMarks $mark): RedirectResponse
    {
        $mark->delete();

        return back()->with('status', 'Practical marks deleted.');
    }

    private function lookups(): array
    {
        return [
            'configsList' => HallTicketConfig::with('exam')->latest('config_id')->get(),
            'exams' => $this->accessScope->applyToExams(Exam::query(), request()->user())->latest('exam_id')->get(['exam_id', 'exam_name', 'semester_id']),
            'colleges' => $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']),
            'departments' => $this->accessScope->applyToDepartments(Department::query(), request()->user())->orderBy('name')->get(['dept_id', 'name']),
            'subjects' => $this->accessScope->applyToSubjects(Subject::query(), request()->user())->orderBy('code')->get(['subject_id', 'code', 'name']),
            'students' => $this->accessScope->applyToStudents(Student::query(), request()->user())->orderBy('enrollment_no')->get(['student_id', 'enrollment_no', 'first_name', 'last_name']),
            'staffMembers' => $this->accessScope->applyToStaff(Staff::query(), request()->user())->orderBy('first_name')->get(['staff_id', 'first_name', 'last_name']),
            'roomsList' => ExamRoom::orderBy('room_no')->get(['room_id', 'room_no', 'building']),
            'ticketsList' => $this->accessScope->applyToHallTickets(HallTicket::with('student'), request()->user())->latest('hall_ticket_id')->get(),
            'theorySchedules' => TheoryExamSchedule::with(['exam', 'subject'])->whereHas('exam', fn ($exam) => $this->accessScope->applyToExams($exam, request()->user()))->latest('schedule_id')->get(),
            'practicalSchedulesList' => PracticalExamSchedule::with(['exam', 'subject'])->whereHas('exam', fn ($exam) => $this->accessScope->applyToExams($exam, request()->user()))->latest('prac_schedule_id')->get(),
            'practicalBatchesList' => PracticalBatch::with('schedule.subject')->latest('batch_id')->get(),
            'academicYears' => $this->accessScope->applyToAcademicYears(AcademicYear::query(), request()->user())->orderByDesc('is_current')->get(['academic_year_id', 'label']),
        ];
    }
}
