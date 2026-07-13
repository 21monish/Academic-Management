<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AttendanceSummary;
use App\Models\College;
use App\Models\Department;
use App\Models\Exam;
use App\Models\FeePayment;
use App\Models\HallTicket;
use App\Models\Lecture;
use App\Models\Notice;
use App\Models\Result;
use App\Models\Staff;
use App\Models\StaffSubjectAssignment;
use App\Models\Student;
use App\Models\StudentFeeLedger;
use App\Models\Subject;
use App\Models\TimetableSlot;
use App\Models\University;
use App\Models\User;
use App\Services\AccessScopeService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $roleName = $user?->role?->role_name ?? 'User';
        $student = null;
        $staff = null;

        if ($roleName === 'Student') {
            $student = Student::query()
                ->with(['college', 'programme', 'category', 'enrollments.semester', 'enrollments.academicYear'])
                ->where(function ($query) use ($request) {
                    $query->whereHas('userAccount', fn ($sub) => $sub->where('users.user_id', $request->user()?->user_id))
                        ->orWhere('email', $request->user()?->email);
                })
                ->first();
        }

        if (in_array($roleName, ['Teaching Staff', 'Non-Teaching Staff', 'HOD'], true)) {
            $staff = Staff::query()
                ->where(function ($query) use ($user) {
                    $query->where('email', $user?->email)
                        ->orWhereIn('staff_id', User::query()
                            ->where('reference_type', 'Staff')
                            ->where('user_id', $user?->user_id)
                            ->select('reference_id'));
                })
                ->first();
        }

        return view('dashboard.index', [
            'roleName' => $roleName,
            'stats' => $this->statsFor($roleName, $user, $student, $staff),
            'charts' => $this->chartsFor($roleName, $user),
            'analytics' => $this->analytics($user),
            'recentActivity' => $this->recentActivity($roleName),
            'student' => $student,
            'staff' => $staff,
            'dashboardData' => $this->dashboardData($roleName, $user, $student, $staff),
            'pageSections' => $this->pageSections(),
        ]);
    }

    private function pageSections(): array
    {
        return [
            'Institution' => [
                ['label' => 'Colleges', 'route' => 'colleges.index', 'permission' => 'college.view'],
                ['label' => 'Departments', 'route' => 'departments.index', 'permission' => 'department.view'],
                ['label' => 'Users', 'route' => 'users.index', 'permission' => ['user.view', 'user_permission.view', 'user_permission.update']],
                ['label' => 'Roles & Permissions', 'route' => 'roles.index', 'permission' => 'role.view'],
            ],
            'People' => [
                ['label' => 'Staff', 'route' => 'staff.index', 'permission' => 'staff.view'],
                ['label' => 'Students', 'route' => 'students.index', 'permission' => 'student.view'],
                ['label' => 'People Categories', 'route' => 'academic.categories.index', 'permission' => 'category.view'],
            ],
            'Academic' => [
                ['label' => 'Academic Years', 'route' => 'academic.academic-years.index', 'permission' => 'academic_year.view'],
                ['label' => 'Programmes', 'route' => 'academic.programmes.index', 'permission' => 'programme.view'],
                ['label' => 'Semesters', 'route' => 'academic.semesters.index', 'permission' => 'semester.view'],
                ['label' => 'Subjects', 'route' => 'academic.subjects.index', 'permission' => 'subject.view'],
                ['label' => 'Curriculum', 'route' => 'academic.curriculum.index', 'permission' => 'curriculum.view'],
                ['label' => 'Elective Groups', 'route' => 'academic.elective-groups.index', 'permission' => 'elective_group.view'],
            ],
            'Attendance' => [
                ['label' => 'Teaching Staff Subject Assignments', 'route' => 'attendance.assignments', 'permission' => 'staff_assignment.view'],
                ['label' => 'Timetable Slots', 'route' => 'attendance.slots', 'permission' => 'timetable_slot.view'],
                ['label' => 'Lectures', 'route' => 'attendance.lectures', 'permission' => 'lecture.view'],
                ['label' => 'Attendance Summary', 'route' => 'attendance.summaries', 'permission' => 'attendance_summary.view'],
                ['label' => 'Defaulters', 'route' => 'attendance.defaulters', 'permission' => 'attendance_defaulter.view'],
            ],
            'Exams' => [
                ['label' => 'Exams', 'route' => 'exams.index', 'permission' => 'exam.view'],
                ['label' => 'Exam Subjects', 'route' => 'exams.subjects', 'permission' => 'exam_subject.view'],
                ['label' => 'Grade Master', 'route' => 'exams.grades', 'permission' => 'grade.view'],
                ['label' => 'Marks Entry', 'route' => 'exams.marks', 'permission' => ['marks_entry.create', 'marks_entry.update']],
                ['label' => 'Results', 'route' => 'exams.results', 'permission' => 'result.view'],
                ['label' => 'Backlogs', 'route' => 'exams.backlogs', 'permission' => 'backlog.view'],
                ['label' => 'Promotions', 'route' => 'exams.promotions', 'permission' => 'promotion.view'],
                ['label' => 'Hall Ticket Config', 'route' => 'exams.logistics.configs', 'permission' => 'hall_ticket_config.view'],
                ['label' => 'Hall Tickets', 'route' => 'exams.logistics.tickets', 'permission' => 'hall_ticket.view'],
                ['label' => 'Exam Rooms', 'route' => 'exams.logistics.rooms', 'permission' => 'exam_room.view'],
                ['label' => 'Seating', 'route' => 'exams.logistics.seating', 'permission' => 'seating.view'],
                ['label' => 'Invigilators', 'route' => 'exams.logistics.invigilators', 'permission' => 'invigilator.view'],
                ['label' => 'Practical Schedule', 'route' => 'exams.logistics.practical-schedules', 'permission' => 'practical_schedule.view'],
                ['label' => 'Practical Batches', 'route' => 'exams.logistics.practical-batches', 'permission' => 'practical_batch.view'],
                ['label' => 'Practical Marks', 'route' => 'exams.logistics.practical-marks', 'permission' => 'practical_mark.view'],
            ],
            'Fees' => [
                ['label' => 'Fee Categories', 'route' => 'fees.categories', 'permission' => 'fee_category.view'],
                ['label' => 'Fee Structures', 'route' => 'fees.structures', 'permission' => 'fee_structure.view'],
                ['label' => 'Student Ledgers', 'route' => 'fees.ledgers', 'permission' => 'student_ledger.view'],
                ['label' => 'Fee Collection', 'route' => 'fees.collections', 'permission' => ['fee_collection.view', 'fee_collection.create', 'fee_collection.update']],
                ['label' => 'Receipts', 'route' => 'fees.receipts', 'permission' => 'receipt.view'],
                ['label' => 'Concessions', 'route' => 'fees.concessions', 'permission' => 'concession.view'],
                ['label' => 'Scholarships', 'route' => 'fees.scholarships', 'permission' => 'scholarship.view'],
                ['label' => 'Fee Reports', 'route' => 'fees.reports', 'permission' => 'fee_report.view'],
            ],
            'Leave' => [
                ['label' => 'Leave Types', 'route' => 'leave.types', 'permission' => 'leave_type.view'],
                ['label' => 'Leave Balances', 'route' => 'leave.balances', 'permission' => 'leave_balance.view'],
                ['label' => 'Applications', 'route' => 'leave.applications', 'permission' => 'leave_application.view'],
                ['label' => 'Approvals', 'route' => 'leave.approvals', 'permission' => ['leave_approval.approve', 'leave_approval.update']],
                ['label' => 'Cancellations', 'route' => 'leave.cancellations', 'permission' => 'leave_cancellation.view'],
                ['label' => 'Substitutes', 'route' => 'leave.substitutes', 'permission' => 'leave_substitute.view'],
                ['label' => 'Holiday Calendar', 'route' => 'leave.holidays', 'permission' => 'holiday.view'],
            ],
            'Notices' => [
                ['label' => 'Notice Categories', 'route' => 'notices.categories', 'permission' => 'notice_category.view'],
                ['label' => 'Notices', 'route' => 'notices.index', 'permission' => 'notice.view'],
                ['label' => 'Audience', 'route' => 'notices.audiences', 'permission' => ['notice_audience.view', 'notice_audience.create', 'notice_audience.update', 'notice_audience.approve']],
                ['label' => 'Attachments', 'route' => 'notices.attachments', 'permission' => 'notice_attachment.view'],
                ['label' => 'Acknowledgements', 'route' => 'notices.acknowledgements', 'permission' => ['notice_acknowledgement.view', 'notice_acknowledgement.create', 'notice_acknowledgement.update', 'notice_acknowledgement.approve']],
            ],
            'Reports' => [
                ['label' => 'Student Reports', 'route' => 'reports.students', 'permission' => 'student_report.view'],
                ['label' => 'Attendance Reports', 'route' => 'reports.attendance', 'permission' => 'attendance_report.view'],
                ['label' => 'Result Cards', 'route' => 'reports.results', 'permission' => 'result_card.view'],
                ['label' => 'Fee Receipts', 'route' => 'reports.fee-receipts', 'permission' => 'fee_receipt_report.view'],
                ['label' => 'Hall Ticket PDF', 'route' => 'reports.hall-tickets', 'permission' => 'hall_ticket_report.view'],
                ['label' => 'Staff Reports', 'route' => 'reports.staff', 'permission' => 'staff_report.view'],
            ],
        ];
    }

    private function statsFor(string $roleName, ?User $user = null, ?Student $student = null, ?Staff $staff = null): array
    {
        $scope = $this->accessScope();

        if ($roleName === 'Student') {
            return [
                'attendanceAverage' => $student ? round((float) AttendanceSummary::where('student_id', $student->student_id)->avg('attendance_percentage'), 2) : 0,
                'publishedResults' => $student ? Result::where('student_id', $student->student_id)->where('is_published', true)->count() : 0,
                'feeBalance' => $student ? (float) StudentFeeLedger::where('student_id', $student->student_id)->sum('balance_due') : 0,
                'hallTickets' => $student ? HallTicket::where('student_id', $student->student_id)->count() : 0,
            ];
        }

        if ($roleName === 'Teaching Staff') {
            return [
                'assignedSubjects' => $staff ? StaffSubjectAssignment::where('staff_id', $staff->staff_id)->where('is_active', true)->count() : 0,
                'lectures' => $staff ? $scope->applyToLectures(Lecture::where('staff_id', $staff->staff_id), $user)->count() : 0,
                'attendanceMarked' => $staff ? $scope->applyToLectures(Lecture::where('staff_id', $staff->staff_id), $user)->whereHas('attendances')->count() : 0,
                'resultsEntered' => $staff ? $scope->applyToResults(Result::query(), $user)
                    ->whereHas('examSubject.subject', fn ($query) => $query->whereIn('subject_id', StaffSubjectAssignment::where('staff_id', $staff->staff_id)->select('subject_id')))
                    ->count() : 0,
            ];
        }

        if ($roleName === 'HOD') {
            $deptId = $staff?->dept_id;

            return [
                'departmentStaff' => $deptId ? $scope->applyToStaff(Staff::where('dept_id', $deptId), $user)->count() : 0,
                'subjects' => $deptId ? $scope->applyToSubjects(Subject::where('dept_id', $deptId), $user)->count() : 0,
                'students' => $deptId ? $scope->applyToStudents(Student::whereHas('programme', fn ($query) => $query->where('dept_id', $deptId)), $user)->count() : 0,
                'attendanceAverage' => $deptId ? round((float) $scope->applyToAttendanceSummaries(AttendanceSummary::whereHas('subject', fn ($query) => $query->where('dept_id', $deptId)), $user)->avg('attendance_percentage'), 2) : 0,
            ];
        }

        if ($roleName === 'University Admin') {
            return [
                'colleges' => $scope->applyToColleges(College::query(), $user)->count(),
                'departments' => $scope->applyToDepartments(Department::query(), $user)->count(),
                'staff' => $scope->applyToStaff(Staff::query(), $user)->count(),
                'students' => $scope->applyToStudents(Student::query(), $user)->count(),
                'exams' => $scope->applyToExams(Exam::query(), $user)->count(),
                'publishedNotices' => $this->applyNoticeScope(Notice::where('is_published', true), $user)->count(),
            ];
        }

        if ($roleName === 'Principal') {
            return [
                'departments' => $scope->applyToDepartments(Department::query(), $user)->count(),
                'staff' => $scope->applyToStaff(Staff::query(), $user)->count(),
                'students' => $scope->applyToStudents(Student::query(), $user)->count(),
                'feeCollected' => (float) $scope->applyToFeePayments(FeePayment::where('payment_status', 'Cleared'), $user)->sum('amount_paid'),
                'publishedNotices' => $this->applyNoticeScope(Notice::where('is_published', true), $user)->count(),
            ];
        }

        if ($roleName !== 'Super Admin') {
            return [
                'notices' => $this->applyNoticeScope(Notice::where('is_published', true), $user)->count(),
            ];
        }

        return [
            'universities' => $scope->applyToUniversities(University::query(), $user)->count(),
            'colleges' => $scope->applyToColleges(College::query(), $user)->count(),
            'departments' => $scope->applyToDepartments(Department::query(), $user)->count(),
            'staff' => $scope->applyToStaff(Staff::query(), $user)->count(),
            'students' => $scope->applyToStudents(Student::query(), $user)->count(),
            'users' => $scope->applyToUsers(User::query(), $user)->count(),
            'exams' => $scope->applyToExams(Exam::query(), $user)->count(),
            'notices' => $this->applyNoticeScope(Notice::query(), $user)->count(),
            'hallTickets' => $scope->applyToHallTickets(HallTicket::query(), $user)->count(),
        ];
    }

    private function dashboardData(string $roleName, ?User $user, ?Student $student, ?Staff $staff): array
    {
        return match ($roleName) {
            'Student' => $this->studentDashboardData($student),
            'Teaching Staff', 'Non-Teaching Staff' => $this->staffDashboardData($staff),
            'HOD' => $this->hodDashboardData($staff),
            'University Admin' => $this->universityDashboardData($user),
            'Principal' => $this->collegeDashboardData($user),
            default => [
                'quickLinks' => $this->quickLinks($roleName),
                'notices' => $this->recentNotices($user),
            ],
        };
    }

    private function studentDashboardData(?Student $student): array
    {
        if (! $student) {
            return ['quickLinks' => $this->quickLinks('Student'), 'notices' => $this->recentNotices()];
        }

        return [
            'quickLinks' => $this->quickLinks('Student'),
            'notices' => $this->recentNoticesForStudent($student),
            'attendance' => AttendanceSummary::with(['subject', 'semester'])
                ->where('student_id', $student->student_id)
                ->orderBy('attendance_percentage')
                ->limit(5)
                ->get(),
            'results' => Result::with(['examSubject.exam', 'examSubject.subject'])
                ->where('student_id', $student->student_id)
                ->where('is_published', true)
                ->latest('result_id')
                ->limit(5)
                ->get(),
            'fees' => StudentFeeLedger::with(['feeStructure.feeCategory', 'semester'])
                ->where('student_id', $student->student_id)
                ->latest('ledger_id')
                ->limit(4)
                ->get(),
        ];
    }

    private function staffDashboardData(?Staff $staff): array
    {
        if (! $staff) {
            return ['quickLinks' => $this->quickLinks('Teaching Staff'), 'notices' => $this->recentNotices()];
        }

        return [
            'quickLinks' => $this->quickLinks(in_array($staff->staff_type, ['Teaching', 'Both'], true) ? 'Teaching Staff' : 'Non-Teaching Staff'),
            'notices' => $this->recentNoticesForStaff($staff),
            'assignments' => StaffSubjectAssignment::with(['subject', 'semester'])
                ->where('staff_id', $staff->staff_id)
                ->where('is_active', true)
                ->limit(5)
                ->get(),
            'lectures' => Lecture::with(['subject', 'slot'])
                ->where('staff_id', $staff->staff_id)
                ->latest('lecture_date')
                ->limit(5)
                ->get(),
        ];
    }

    private function hodDashboardData(?Staff $staff): array
    {
        $scope = $this->accessScope();
        $deptId = $staff?->dept_id;

        return [
            'quickLinks' => $this->quickLinks('HOD'),
            'notices' => $this->recentNoticesForStaff($staff),
            'staffMembers' => $deptId ? $scope->applyToStaff(Staff::where('dept_id', $deptId), auth()->user())->latest('staff_id')->limit(5)->get() : collect(),
            'subjects' => $deptId ? $scope->applyToSubjects(Subject::where('dept_id', $deptId), auth()->user())->orderBy('code')->limit(6)->get() : collect(),
            'lowAttendance' => $deptId ? $scope->applyToAttendanceSummaries(AttendanceSummary::with(['student', 'subject'])
                ->whereHas('subject', fn ($query) => $query->where('dept_id', $deptId)), auth()->user())
                ->where('attendance_percentage', '<', 75)
                ->orderBy('attendance_percentage')
                ->limit(5)
                ->get() : collect(),
        ];
    }

    private function collegeDashboardData(?User $user): array
    {
        $scope = $this->accessScope();

        return [
            'quickLinks' => $this->quickLinks('Principal'),
            'notices' => $this->recentNotices($user),
            'departments' => $scope->applyToDepartments(Department::query(), $user)
                ->withCount(['staff', 'programmes'])
                ->orderBy('name')
                ->limit(6)
                ->get(),
            'recentStudents' => $scope->applyToStudents(Student::query(), $user)
                ->with('programme')
                ->latest('student_id')
                ->limit(5)
                ->get(),
            'recentExams' => $scope->applyToExams(Exam::query(), $user)
                ->with('semester')
                ->latest('exam_id')
                ->limit(5)
                ->get(),
        ];
    }

    private function universityDashboardData(?User $user): array
    {
        $scope = $this->accessScope();

        return [
            'quickLinks' => $this->quickLinks('University Admin'),
            'notices' => $this->recentNotices($user),
            'colleges' => $scope->applyToColleges(College::query(), $user)
                ->withCount(['departments', 'staff', 'students'])
                ->orderBy('name')
                ->limit(6)
                ->get(),
            'recentStudents' => $scope->applyToStudents(Student::query(), $user)
                ->with(['college', 'programme'])
                ->latest('student_id')
                ->limit(5)
                ->get(),
            'recentExams' => $scope->applyToExams(Exam::query(), $user)
                ->with(['college', 'semester'])
                ->latest('exam_id')
                ->limit(5)
                ->get(),
        ];
    }

    private function quickLinks(string $roleName): array
    {
        $links = [
            'Student' => [
                ['label' => 'My Profile', 'route' => 'profile.edit'],
                ['label' => 'Notices', 'route' => 'notices.index'],
            ],
            'Teaching Staff' => [
                ['label' => 'Lectures', 'route' => 'attendance.lectures'],
                ['label' => 'Attendance Summary', 'route' => 'attendance.summaries'],
                ['label' => 'Marks Entry', 'route' => 'exams.marks'],
                ['label' => 'Notices', 'route' => 'notices.index'],
            ],
            'Non-Teaching Staff' => [
                ['label' => 'Students', 'route' => 'students.index'],
                ['label' => 'Attendance', 'route' => 'attendance.summaries'],
                ['label' => 'Leave', 'route' => 'leave.applications'],
                ['label' => 'Notices', 'route' => 'notices.index'],
            ],
            'HOD' => [
                ['label' => 'Staff', 'route' => 'staff.index'],
                ['label' => 'Subjects', 'route' => 'academic.subjects.index'],
                ['label' => 'Attendance', 'route' => 'attendance.summaries'],
                ['label' => 'Results', 'route' => 'exams.results'],
            ],
            'University Admin' => [
                ['label' => 'Colleges', 'route' => 'colleges.index'],
                ['label' => 'Departments', 'route' => 'departments.index'],
                ['label' => 'Students', 'route' => 'students.index'],
                ['label' => 'Reports', 'route' => 'reports.students'],
            ],
            'Principal' => [
                ['label' => 'Departments', 'route' => 'departments.index'],
                ['label' => 'Students', 'route' => 'students.index'],
                ['label' => 'Staff', 'route' => 'staff.index'],
                ['label' => 'Reports', 'route' => 'reports.students'],
            ],
        ];

        return array_values(array_filter($links[$roleName] ?? [], fn ($link) => \Illuminate\Support\Facades\Route::has($link['route'])));
    }

    private function recentNotices(?User $user = null)
    {
        return $this->applyNoticeScope(Notice::with('category')->where('is_published', true), $user)
            ->latest('notice_id')
            ->limit(5)
            ->get();
    }

    private function recentNoticesForStudent(?Student $student)
    {
        if (! $student) {
            return $this->recentNotices(auth()->user());
        }

        return Notice::with('category')
            ->where('is_published', true)
            ->where(function ($query) use ($student) {
                $query->where('college_id', $student->college_id)
                    ->orWhereNull('college_id');
            })
            ->where(function ($query) use ($student) {
                $query->whereNull('dept_id')
                    ->orWhereHas('department.programmes', fn ($programme) => $programme->where('programme_id', $student->programme_id));
            })
            ->latest('notice_id')
            ->limit(5)
            ->get();
    }

    private function recentNoticesForStaff(?Staff $staff)
    {
        if (! $staff) {
            return $this->recentNotices(auth()->user());
        }

        return Notice::with('category')
            ->where('is_published', true)
            ->where(function ($query) use ($staff) {
                $query->where('college_id', $staff->college_id)
                    ->orWhereNull('college_id');
            })
            ->where(function ($query) use ($staff) {
                $query->whereNull('dept_id')
                    ->orWhere('dept_id', $staff->dept_id);
            })
            ->latest('notice_id')
            ->limit(5)
            ->get();
    }

    private function chartsFor(string $roleName, ?User $user): array
    {
        if ($roleName !== 'Super Admin') {
            return [];
        }

        $scope = $this->accessScope();
        $feeCollected = (float) $scope->applyToFeePayments(FeePayment::where('payment_status', 'Cleared'), $user)->sum('amount_paid');
        $attendanceAverage = (float) $scope->applyToAttendanceSummaries(AttendanceSummary::query(), $user)->avg('attendance_percentage');
        $publishedResults = $scope->applyToResults(Result::where('is_published', true), $user)->count();
        $totalResults = max($scope->applyToResults(Result::query(), $user)->count(), 1);

        return [
            'feeCollected' => $feeCollected,
            'attendanceAverage' => round($attendanceAverage, 2),
            'resultPublishedPct' => round(($publishedResults / $totalResults) * 100, 2),
            'hallTicketGenerated' => $scope->applyToHallTickets(HallTicket::where('generated', true), $user)->count(),
        ];
    }

    private function analytics(?User $user): array
    {
        $scope = $this->accessScope();
        $studentTotal = $scope->applyToStudents(Student::query(), $user)->count();
        $studentActive = $scope->applyToStudents(Student::where('is_active', true), $user)->count();
        $staffTotal = $scope->applyToStaff(Staff::query(), $user)->count();
        $staffActive = $scope->applyToStaff(Staff::where('is_active', true), $user)->count();
        $attendanceAverage = round((float) $scope->applyToAttendanceSummaries(AttendanceSummary::query(), $user)->avg('attendance_percentage'), 2);
        $resultTotal = $scope->applyToResults(Result::query(), $user)->count();
        $publishedResults = $scope->applyToResults(Result::where('is_published', true), $user)->count();
        $feeNetPayable = (float) $scope->applyToFeeLedgers(StudentFeeLedger::query(), $user)->sum(DB::raw('COALESCE(net_payable, total_amount, 0)'));
        $feePaid = (float) $scope->applyToFeeLedgers(StudentFeeLedger::query(), $user)->sum('amount_paid');
        $hallTicketTotal = $scope->applyToHallTickets(HallTicket::query(), $user)->count();
        $hallTicketGenerated = $scope->applyToHallTickets(HallTicket::where('generated', true), $user)->count();

        return [
            'cards' => [
                [
                    'label' => 'Student Active Rate',
                    'value' => $this->percentage($studentActive, $studentTotal),
                    'detail' => number_format($studentActive).' of '.number_format($studentTotal).' students active',
                    'permission' => 'student.view',
                    'color' => '#0891b2',
                ],
                [
                    'label' => 'Staff Active Rate',
                    'value' => $this->percentage($staffActive, $staffTotal),
                    'detail' => number_format($staffActive).' of '.number_format($staffTotal).' staff active',
                    'permission' => 'staff.view',
                    'color' => '#0f766e',
                ],
                [
                    'label' => 'Attendance Average',
                    'value' => $attendanceAverage,
                    'detail' => 'Average across attendance summaries',
                    'permission' => ['attendance_summary.view', 'attendance_report.view'],
                    'color' => '#16a34a',
                ],
                [
                    'label' => 'Fee Recovery',
                    'value' => $this->percentage($feePaid, $feeNetPayable),
                    'detail' => 'INR '.number_format($feePaid, 2).' paid of INR '.number_format($feeNetPayable, 2),
                    'permission' => ['student_ledger.view', 'fee_collection.view', 'fee_report.view'],
                    'color' => '#9333ea',
                ],
                [
                    'label' => 'Results Published',
                    'value' => $this->percentage($publishedResults, $resultTotal),
                    'detail' => number_format($publishedResults).' of '.number_format($resultTotal).' result records published',
                    'permission' => ['result.view', 'result_card.view'],
                    'color' => '#ea580c',
                ],
                [
                    'label' => 'Hall Ticket Progress',
                    'value' => $this->percentage($hallTicketGenerated, $hallTicketTotal),
                    'detail' => number_format($hallTicketGenerated).' of '.number_format($hallTicketTotal).' hall tickets generated',
                    'permission' => ['hall_ticket.view', 'hall_ticket_report.view'],
                    'color' => '#2563eb',
                ],
            ],
            'charts' => [
                [
                    'title' => 'People Mix',
                    'subtitle' => 'Students, staff, and login accounts in the system',
                    'format' => 'number',
                    'items' => [
                        ['label' => 'Students', 'value' => $studentTotal, 'permission' => 'student.view', 'color' => 'bg-cyan-600'],
                        ['label' => 'Staff', 'value' => $staffTotal, 'permission' => 'staff.view', 'color' => 'bg-teal-600'],
                        ['label' => 'Users', 'value' => $scope->applyToUsers(User::query(), $user)->count(), 'permission' => ['user.view', 'user_permission.view'], 'color' => 'bg-slate-700'],
                    ],
                ],
                [
                    'title' => 'Student Status',
                    'subtitle' => 'Active vs inactive student records',
                    'permission' => 'student.view',
                    'format' => 'number',
                    'items' => [
                        ['label' => 'Active', 'value' => $studentActive, 'color' => 'bg-emerald-600'],
                        ['label' => 'Inactive', 'value' => max($studentTotal - $studentActive, 0), 'color' => 'bg-amber-500'],
                    ],
                ],
                [
                    'title' => 'Staff Type',
                    'subtitle' => 'Teaching and non-teaching staff distribution',
                    'permission' => 'staff.view',
                    'format' => 'number',
                    'items' => $this->countSeries($scope->applyToStaff(Staff::query(), $user), 'staff_type', [
                        'Teaching' => 'bg-cyan-600',
                        'Non-Teaching' => 'bg-indigo-600',
                        'Both' => 'bg-emerald-600',
                    ]),
                ],
                [
                    'title' => 'Attendance Bands',
                    'subtitle' => 'Attendance summaries grouped by risk level',
                    'permission' => ['attendance_summary.view', 'attendance_report.view'],
                    'format' => 'number',
                    'items' => [
                        ['label' => '90% and above', 'value' => $scope->applyToAttendanceSummaries(AttendanceSummary::where('attendance_percentage', '>=', 90), $user)->count(), 'color' => 'bg-emerald-600'],
                        ['label' => '75% to 89%', 'value' => $scope->applyToAttendanceSummaries(AttendanceSummary::whereBetween('attendance_percentage', [75, 89.99]), $user)->count(), 'color' => 'bg-cyan-600'],
                        ['label' => 'Below 75%', 'value' => $scope->applyToAttendanceSummaries(AttendanceSummary::where('attendance_percentage', '<', 75), $user)->count(), 'color' => 'bg-red-500'],
                        ['label' => 'No Percentage', 'value' => $scope->applyToAttendanceSummaries(AttendanceSummary::whereNull('attendance_percentage'), $user)->count(), 'color' => 'bg-slate-400'],
                    ],
                ],
                [
                    'title' => 'Fee Ledger Status',
                    'subtitle' => 'Student ledgers by payment status',
                    'permission' => ['student_ledger.view', 'fee_report.view'],
                    'format' => 'number',
                    'items' => $this->countSeries($scope->applyToFeeLedgers(StudentFeeLedger::query(), $user), 'payment_status', [
                        'Paid' => 'bg-emerald-600',
                        'Partial' => 'bg-cyan-600',
                        'Unpaid' => 'bg-amber-500',
                        'Overdue' => 'bg-red-500',
                    ]),
                ],
                [
                    'title' => 'Fee Collection Modes',
                    'subtitle' => 'Cleared payment amount by mode',
                    'permission' => ['fee_collection.view', 'receipt.view', 'fee_report.view'],
                    'format' => 'money',
                    'items' => $this->sumSeries(
                        $scope->applyToFeePayments(FeePayment::where('payment_status', 'Cleared'), $user),
                        'payment_mode',
                        'amount_paid',
                        [
                            'Cash' => 'bg-emerald-600',
                            'Online' => 'bg-cyan-600',
                            'Cheque' => 'bg-indigo-600',
                            'DD' => 'bg-violet-600',
                            'NEFT' => 'bg-slate-700',
                        ]
                    ),
                ],
                [
                    'title' => 'Result Status',
                    'subtitle' => 'Exam result outcomes',
                    'permission' => ['result.view', 'result_card.view'],
                    'format' => 'number',
                    'items' => $this->countSeries($scope->applyToResults(Result::query(), $user), 'result_status', [
                        'Pass' => 'bg-emerald-600',
                        'Fail' => 'bg-red-500',
                        'ATKT' => 'bg-amber-500',
                        'Absent' => 'bg-slate-500',
                    ]),
                ],
                [
                    'title' => 'Hall Ticket Status',
                    'subtitle' => 'Hall tickets across workflow stages',
                    'permission' => ['hall_ticket.view', 'hall_ticket_report.view'],
                    'format' => 'number',
                    'items' => $this->countSeries($scope->applyToHallTickets(HallTicket::query(), $user), 'status', [
                        'Draft' => 'bg-slate-500',
                        'Generated' => 'bg-emerald-600',
                        'Downloaded' => 'bg-cyan-600',
                        'Revoked' => 'bg-red-500',
                    ]),
                ],
                [
                    'title' => 'Notice Publishing',
                    'subtitle' => 'Published and draft notices',
                    'permission' => 'notice.view',
                    'format' => 'number',
                    'items' => [
                        ['label' => 'Published', 'value' => $this->applyNoticeScope(Notice::where('is_published', true), $user)->count(), 'color' => 'bg-emerald-600'],
                        ['label' => 'Draft / Hidden', 'value' => $this->applyNoticeScope(Notice::where('is_published', false), $user)->count(), 'color' => 'bg-slate-500'],
                    ],
                ],
            ],
        ];
    }

    private function countSeries(Builder $query, string $column, array $labels): array
    {
        $counts = $query
            ->select($column, DB::raw('count(*) as aggregate_count'))
            ->groupBy($column)
            ->pluck('aggregate_count', $column);

        return collect($labels)
            ->map(fn (string $color, string $label) => [
                'label' => $label,
                'value' => (float) ($counts[$label] ?? 0),
                'color' => $color,
            ])
            ->values()
            ->all();
    }

    private function sumSeries($query, string $groupColumn, string $sumColumn, array $labels): array
    {
        $sums = $query
            ->select($groupColumn, DB::raw("COALESCE(SUM({$sumColumn}), 0) as aggregate_sum"))
            ->groupBy($groupColumn)
            ->pluck('aggregate_sum', $groupColumn);

        return collect($labels)
            ->map(fn (string $color, string $label) => [
                'label' => $label,
                'value' => (float) ($sums[$label] ?? 0),
                'color' => $color,
            ])
            ->values()
            ->all();
    }

    private function applyNoticeScope(Builder $query, ?User $user): Builder
    {
        $scope = $this->accessScope()->forUser($user);

        return match ($scope['level']) {
            'university' => $query->where(function ($notices) use ($scope) {
                $notices->whereNull('college_id')
                    ->orWhereHas('college', fn ($college) => $college->where('university_id', $scope['university_id']));
            }),
            'college' => $query
                ->where(function ($notices) use ($scope) {
                    $notices->whereNull('college_id')
                        ->orWhere('college_id', $scope['college_id']);
                })
                ->whereNull('dept_id'),
            'programme', 'subject_semester' => $query
                ->where(function ($notices) use ($scope) {
                    $notices->whereNull('college_id')
                        ->orWhere('college_id', $scope['college_id']);
                })
                ->where(function ($notices) use ($scope) {
                    $notices->whereNull('dept_id')
                        ->orWhere('dept_id', $scope['dept_id']);
                }),
            default => $query,
        };
    }

    private function accessScope(): AccessScopeService
    {
        return app(AccessScopeService::class);
    }

    private function percentage(float|int $value, float|int $total): float
    {
        if ((float) $total <= 0.0) {
            return 0.0;
        }

        return round(((float) $value / (float) $total) * 100, 2);
    }

    private function recentActivity(string $roleName)
    {
        if ($roleName !== 'Super Admin' || ! Schema::hasTable('activity_logs')) {
            return collect();
        }

        return ActivityLog::with('user')
            ->latest('activity_log_id')
            ->limit(8)
            ->get();
    }
}
