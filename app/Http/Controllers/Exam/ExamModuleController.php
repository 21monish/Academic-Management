<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Backlog;
use App\Models\College;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\GradeMaster;
use App\Models\Programme;
use App\Models\Result;
use App\Models\Semester;
use App\Models\SemesterResultSummary;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentPromotion;
use App\Models\Subject;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamModuleController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function index(): View
    {
        return view('exams.index', array_merge($this->lookups(), [
            'exams' => $this->accessScope->applyToExams(Exam::with(['academicYear', 'semester', 'college']), request()->user())
                ->latest('exam_id')
                ->paginate(15),
        ]));
    }

    public function storeExam(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'semester_id' => ['required', 'exists:semesters,semester_id'],
            'college_id' => ['required', 'exists:colleges,college_id'],
            'exam_name' => ['required', 'string', 'max:150'],
            'exam_type' => ['required', 'in:MidSem,EndSem,Remedial,Backlog'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_published' => ['boolean'],
        ]);

        abort_unless($this->accessScope->applyToColleges(College::whereKey($data['college_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['semester_id']), $request->user())->exists(), 403);

        Exam::create($data);

        return back()->with('status', 'Exam saved.');
    }

    public function destroyExam(Exam $exam): RedirectResponse
    {
        abort_unless($this->accessScope->applyToExams(Exam::whereKey($exam->exam_id), request()->user())->exists(), 403);

        $exam->delete();

        return back()->with('status', 'Exam deleted.');
    }

    public function subjects(): View
    {
        return view('exams.subjects.index', array_merge($this->lookups(), [
            'examSubjects' => $this->accessScope->applyToExamSubjects(ExamSubject::with(['exam', 'subject']), request()->user())
                ->latest('exam_subject_id')
                ->paginate(15),
        ]));
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'exam_id' => ['required', 'exists:exams,exam_id'],
            'subject_id' => ['required', 'exists:subjects,subject_id'],
            'exam_date' => ['nullable', 'date'],
            'exam_time' => ['nullable', 'date_format:H:i'],
            'max_theory_marks' => ['nullable', 'integer', 'min:0'],
            'max_practical_marks' => ['nullable', 'integer', 'min:0'],
            'max_internal_marks' => ['nullable', 'integer', 'min:0'],
            'passing_theory_marks' => ['nullable', 'integer', 'min:0'],
            'passing_practical_marks' => ['nullable', 'integer', 'min:0'],
            'passing_internal_marks' => ['nullable', 'integer', 'min:0'],
        ]);

        abort_unless($this->accessScope->applyToExams(Exam::whereKey($data['exam_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($data['subject_id']), $request->user())->exists(), 403);

        ExamSubject::create($data);

        return back()->with('status', 'Exam subject saved.');
    }

    public function destroySubject(ExamSubject $examSubject): RedirectResponse
    {
        abort_unless($this->accessScope->applyToExamSubjects(ExamSubject::whereKey($examSubject->exam_subject_id), request()->user())->exists(), 403);

        $examSubject->delete();

        return back()->with('status', 'Exam subject deleted.');
    }

    public function grades(): View
    {
        return view('exams.grades.index', array_merge($this->lookups(), [
            'grades' => GradeMaster::with('programme')
                ->whereHas('programme', fn ($programme) => $this->accessScope->applyToProgrammes($programme, request()->user()))
                ->orderBy('programme_id')
                ->orderByDesc('grade_point')
                ->paginate(15),
        ]));
    }

    public function storeGrade(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'programme_id' => ['required', 'exists:programmes,programme_id'],
            'grade_letter' => ['required', 'string', 'max:5'],
            'min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_percentage'],
            'grade_point' => ['required', 'numeric', 'min:0', 'max:10'],
            'description' => ['nullable', 'string', 'max:100'],
        ]);

        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($data['programme_id']), $request->user())->exists(), 403);

        GradeMaster::create($data);

        return back()->with('status', 'Grade saved.');
    }

    public function destroyGrade(GradeMaster $grade): RedirectResponse
    {
        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($grade->programme_id), request()->user())->exists(), 403);

        $grade->delete();

        return back()->with('status', 'Grade deleted.');
    }

    public function marks(Request $request): View
    {
        $examSubject = $request->filled('exam_subject_id')
            ? ExamSubject::with(['exam.semester', 'subject'])->find($request->integer('exam_subject_id'))
            : null;
        $students = collect();

        if ($examSubject) {
            abort_unless($this->accessScope->applyToExamSubjects(ExamSubject::whereKey($examSubject->exam_subject_id), $request->user())->exists(), 403);

            $students = $this->accessScope->applyToStudents(
                Student::with(['enrollments' => fn ($query) => $query->where('semester_id', $examSubject->exam->semester_id)]),
                $request->user()
            )
                ->whereHas('enrollments', fn ($query) => $query->where('semester_id', $examSubject->exam->semester_id))
                ->orderBy('enrollment_no')
                ->get();
        }

        return view('exams.results.marks', $this->lookups() + compact('examSubject', 'students'));
    }

    public function storeMarks(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'exam_subject_id' => ['required', 'exists:exam_subjects,exam_subject_id'],
            'marks' => ['required', 'array'],
            'marks.*.enrollment_id' => ['required', 'exists:student_enrollments,enrollment_id'],
            'marks.*.theory_marks' => ['nullable', 'numeric', 'min:0'],
            'marks.*.practical_marks' => ['nullable', 'numeric', 'min:0'],
            'marks.*.internal_marks' => ['nullable', 'numeric', 'min:0'],
            'marks.*.result_status' => ['nullable', 'in:Pass,Fail,ATKT,Absent'],
        ]);

        $examSubject = ExamSubject::with(['exam.semester', 'subject'])->findOrFail($data['exam_subject_id']);
        abort_unless($this->accessScope->applyToExamSubjects(ExamSubject::whereKey($examSubject->exam_subject_id), $request->user())->exists(), 403);

        DB::transaction(function () use ($data, $examSubject, $request) {
            foreach ($data['marks'] as $studentId => $row) {
                abort_unless($this->accessScope->applyToStudents(Student::whereKey($studentId), $request->user())->exists(), 403);

                $total = array_sum(array_filter([
                    $row['theory_marks'] ?? null,
                    $row['practical_marks'] ?? null,
                    $row['internal_marks'] ?? null,
                ], fn ($value) => $value !== null && $value !== ''));
                $max = array_sum(array_filter([
                    $examSubject->max_theory_marks,
                    $examSubject->max_practical_marks,
                    $examSubject->max_internal_marks,
                ]));
                $percentage = $max > 0 ? round(($total / $max) * 100, 2) : null;
                $grade = $this->gradeFor((int) $studentId, $percentage);
                $status = $row['result_status'] ?: ($this->passes($examSubject, $row) ? 'Pass' : 'Fail');

                Result::updateOrCreate(
                    ['student_id' => $studentId, 'exam_subject_id' => $examSubject->exam_subject_id],
                    [
                        'enrollment_id' => $row['enrollment_id'],
                        'theory_marks' => $row['theory_marks'] ?? null,
                        'practical_marks' => $row['practical_marks'] ?? null,
                        'internal_marks' => $row['internal_marks'] ?? null,
                        'total_marks' => $total,
                        'percentage' => $percentage,
                        'grade' => $grade?->grade_letter,
                        'grade_point' => $grade?->grade_point,
                        'result_status' => $status,
                        'is_atkt' => in_array($status, ['ATKT', 'Fail'], true),
                        'declared_at' => now(),
                    ]
                );
            }

            $this->generateSummaries($examSubject->exam);
        });

        return redirect()->route('exams.results')->with('status', 'Marks saved and summaries generated.');
    }

    public function results(): View
    {
        return view('exams.results.index', [
            'summaries' => SemesterResultSummary::with(['student', 'exam', 'semester'])
                ->where(fn ($query) => $this->accessScope->applyToSemesterResultSummaries($query, request()->user()))
                ->latest('summary_id')
                ->paginate(20),
        ]);
    }

    public function backlogs(): View
    {
        return view('exams.backlogs.index', array_merge($this->lookups(), [
            'backlogs' => $this->accessScope->applyToBacklogs(Backlog::with(['student', 'subject', 'originalExam', 'semester']), request()->user())
                ->latest('backlog_id')
                ->paginate(20),
        ]));
    }

    public function storeBacklog(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,student_id'],
            'subject_id' => ['required', 'exists:subjects,subject_id'],
            'original_exam_id' => ['required', 'exists:exams,exam_id'],
            'semester_id' => ['required', 'exists:semesters,semester_id'],
            'backlog_type' => ['required', 'in:ATKT,Regular'],
            'attempt_number' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:Pending,Cleared,Lapsed'],
            'registered_on' => ['nullable', 'date'],
        ]);

        abort_unless($this->accessScope->applyToStudents(Student::whereKey($data['student_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSubjects(Subject::whereKey($data['subject_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToExams(Exam::whereKey($data['original_exam_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['semester_id']), $request->user())->exists(), 403);

        Backlog::create($data);

        return back()->with('status', 'Backlog saved.');
    }

    public function destroyBacklog(Backlog $backlog): RedirectResponse
    {
        abort_unless($this->accessScope->applyToBacklogs(Backlog::whereKey($backlog->backlog_id), request()->user())->exists(), 403);

        $backlog->delete();

        return back()->with('status', 'Backlog deleted.');
    }

    public function promotions(): View
    {
        return view('exams.promotions.index', array_merge($this->lookups(), [
            'students' => $this->accessScope->applyToStudents(Student::query(), request()->user())->orderBy('enrollment_no')->get(['student_id', 'enrollment_no', 'first_name', 'last_name']),
            'staffMembers' => $this->accessScope->applyToStaff(Staff::query(), request()->user())->orderBy('first_name')->get(['staff_id', 'first_name', 'last_name']),
            'promotions' => StudentPromotion::with(['student', 'fromSemester', 'toSemester', 'academicYear'])
                ->whereHas('student', fn ($student) => $this->accessScope->applyToStudents($student, request()->user()))
                ->latest('promotion_id')
                ->paginate(20),
        ]));
    }

    public function storePromotion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,student_id'],
            'from_semester_id' => ['required', 'exists:semesters,semester_id'],
            'to_semester_id' => ['required', 'exists:semesters,semester_id'],
            'academic_year_id' => ['required', 'exists:academic_years,academic_year_id'],
            'promotion_status' => ['required', 'in:Promoted,Detained,Withdrawn'],
            'backlogs_at_promotion' => ['nullable', 'integer', 'min:0'],
            'sgpa_at_promotion' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'attendance_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_manual_override' => ['boolean'],
            'approved_by' => ['nullable', 'exists:staff,staff_id'],
            'remarks' => ['nullable', 'string'],
        ]);

        abort_unless($this->accessScope->applyToStudents(Student::whereKey($data['student_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['from_semester_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(Semester::whereKey($data['to_semester_id']), $request->user())->exists(), 403);
        if (! empty($data['approved_by'])) {
            abort_unless($this->accessScope->applyToStaff(Staff::whereKey($data['approved_by']), $request->user())->exists(), 403);
        }

        StudentPromotion::create($data);

        return back()->with('status', 'Promotion saved.');
    }

    public function destroyPromotion(StudentPromotion $promotion): RedirectResponse
    {
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($promotion->student_id), request()->user())->exists(), 403);

        $promotion->delete();

        return back()->with('status', 'Promotion deleted.');
    }

    private function generateSummaries(Exam $exam): void
    {
        $exam->load('examSubjects');
        $studentIds = Result::whereIn('exam_subject_id', $exam->examSubjects->pluck('exam_subject_id'))->pluck('student_id')->unique();

        foreach ($studentIds as $studentId) {
            $results = Result::where('student_id', $studentId)
                ->whereIn('exam_subject_id', $exam->examSubjects->pluck('exam_subject_id'))
                ->get();
            $total = (float) $results->sum('total_marks');
            $max = (float) $exam->examSubjects->sum(fn ($subject) => ($subject->max_theory_marks ?? 0) + ($subject->max_practical_marks ?? 0) + ($subject->max_internal_marks ?? 0));
            $failed = $results->whereIn('result_status', ['Fail', 'ATKT', 'Absent'])->count();
            $sgpa = $results->avg('grade_point');

            SemesterResultSummary::updateOrCreate(
                ['student_id' => $studentId, 'exam_id' => $exam->exam_id],
                [
                    'semester_id' => $exam->semester_id,
                    'total_marks_obtained' => $total,
                    'total_max_marks' => $max,
                    'sgpa' => $sgpa ? round($sgpa, 2) : null,
                    'cgpa' => $sgpa ? round($sgpa, 2) : null,
                    'total_credits_earned' => $failed > 0 ? 0 : null,
                    'backlogs_count' => $failed,
                    'overall_status' => $failed > 0 ? 'ATKT' : 'Pass',
                ]
            );

            foreach ($results->whereIn('result_status', ['Fail', 'ATKT']) as $result) {
                Backlog::firstOrCreate([
                    'student_id' => $studentId,
                    'subject_id' => $result->examSubject?->subject_id,
                    'original_exam_id' => $exam->exam_id,
                    'semester_id' => $exam->semester_id,
                ], [
                    'backlog_type' => 'ATKT',
                    'attempt_number' => 1,
                    'status' => 'Pending',
                    'registered_on' => now()->toDateString(),
                ]);
            }
        }
    }

    private function gradeFor(int $studentId, ?float $percentage): ?GradeMaster
    {
        if ($percentage === null) {
            return null;
        }

        $programmeId = Student::whereKey($studentId)->value('programme_id');

        return GradeMaster::where('programme_id', $programmeId)
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderByDesc('grade_point')
            ->first();
    }

    private function passes(ExamSubject $subject, array $row): bool
    {
        return ($row['theory_marks'] ?? 0) >= ($subject->passing_theory_marks ?? 0)
            && ($row['practical_marks'] ?? 0) >= ($subject->passing_practical_marks ?? 0)
            && ($row['internal_marks'] ?? 0) >= ($subject->passing_internal_marks ?? 0);
    }

    private function lookups(): array
    {
        return [
            'academicYears' => $this->accessScope->applyToAcademicYears(AcademicYear::query(), request()->user())->orderByDesc('is_current')->orderByDesc('start_date')->get(['academic_year_id', 'label']),
            'colleges' => $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get(['college_id', 'name']),
            'semesters' => $this->accessScope->applyToSemesters(Semester::query(), request()->user())->orderBy('semester_no')->get(['semester_id', 'programme_id', 'semester_no']),
            'programmes' => $this->accessScope->applyToProgrammes(Programme::query(), request()->user())->orderBy('name')->get(['programme_id', 'name']),
            'subjects' => $this->accessScope->applyToSubjects(Subject::query(), request()->user())->orderBy('code')->get(['subject_id', 'code', 'name']),
            'exams' => $this->accessScope->applyToExams(Exam::query(), request()->user())->orderByDesc('exam_id')->get(['exam_id', 'exam_name', 'semester_id']),
            'examSubjects' => $this->accessScope->applyToExamSubjects(ExamSubject::with(['exam', 'subject']), request()->user())->latest('exam_subject_id')->get(),
        ];
    }
}
