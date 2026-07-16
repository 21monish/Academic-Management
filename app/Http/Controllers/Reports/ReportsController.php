<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AttendanceSummary;
use App\Models\FeePayment;
use App\Models\HallTicket;
use App\Models\Result;
use App\Models\Staff;
use App\Models\Student;
use App\Services\AccessScopeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function students(Request $request): View
    {
        $students = $this->accessScope->applyToStudents(
            Student::with(['college', 'programme', 'category', 'enrollments.semester']),
            $request->user()
        )
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where(function ($inner) use ($term) {
                    $inner->where('enrollment_no', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%");
                });
            })
            ->orderBy('enrollment_no')
            ->paginate(25)
            ->withQueryString();

        return view('reports.students', compact('students'));
    }

    public function studentPrint(Student $student): View
    {
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), request()->user())->exists(), 403);

        $student->load(['college.university', 'programme', 'category', 'enrollments.semester', 'feeLedgers', 'hallTickets']);

        return view('reports.print.student', compact('student'));
    }

    public function attendance(Request $request): View
    {
        $summaries = $this->accessScope->applyToAttendanceSummaries(
            AttendanceSummary::with(['student', 'subject', 'semester']),
            $request->user()
        )
            ->when($request->filled('threshold'), fn ($query) => $query->where('attendance_percentage', '<', (float) $request->threshold))
            ->latest('summary_id')
            ->paginate(25)
            ->withQueryString();

        return view('reports.attendance', [
            'summaries' => $summaries,
            'threshold' => $request->input('threshold'),
        ]);
    }

    public function resultCards(Request $request): View
    {
        $results = $this->accessScope->applyToResults(
            Result::with(['student', 'examSubject.exam', 'examSubject.subject', 'enrollment.semester']),
            $request->user()
        )
            ->when($request->filled('student_id'), fn ($query) => $query->where('student_id', $request->integer('student_id')))
            ->latest('result_id')
            ->paginate(25)
            ->withQueryString();

        return view('reports.results', [
            'results' => $results,
            'students' => $this->accessScope->applyToStudents(Student::query(), $request->user())->orderBy('enrollment_no')->get(['student_id', 'enrollment_no', 'first_name', 'last_name']),
        ]);
    }

    public function resultPrint(Student $student): View
    {
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), request()->user())->exists(), 403);

        $student->load(['college.university', 'programme']);
        $results = $this->accessScope->applyToResults(
            Result::with(['examSubject.exam', 'examSubject.subject', 'enrollment.semester']),
            request()->user()
        )
            ->where('student_id', $student->student_id)
            ->latest('result_id')
            ->get();

        return view('reports.print.result-card', compact('student', 'results'));
    }

    public function feeReceipts(Request $request): View
    {
        $receipts = $this->accessScope->applyToFeePayments(
            FeePayment::with(['student', 'ledger.feeStructure.feeCategory', 'collectedBy']),
            $request->user()
        )
            ->when($request->filled('receipt_no'), fn ($query) => $query->where('receipt_no', 'like', '%' . $request->receipt_no . '%'))
            ->latest('payment_id')
            ->paginate(25)
            ->withQueryString();

        return view('reports.fee-receipts', compact('receipts'));
    }

    public function receiptPrint(FeePayment $payment): View
    {
        abort_unless($this->accessScope->applyToFeePayments(FeePayment::whereKey($payment->payment_id), request()->user())->exists(), 403);

        $payment->load(['student.college.university', 'student.programme', 'ledger.feeStructure.feeCategory', 'collectedBy']);

        return view('reports.print.fee-receipt', compact('payment'));
    }

    public function hallTickets(Request $request): View
    {
        $tickets = $this->accessScope->applyToHallTickets(
            HallTicket::with(['config.exam', 'student.programme', 'enrollment.semester']),
            $request->user()
        )
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where('hall_ticket_no', 'like', "%{$term}%")
                    ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('enrollment_no', 'like', "%{$term}%"));
            })
            ->latest('hall_ticket_id')
            ->paginate(25)
            ->withQueryString();

        return view('reports.hall-tickets', compact('tickets'));
    }

    public function hallTicketPrint(HallTicket $ticket): View
    {
        abort_unless($this->accessScope->applyToHallTickets(HallTicket::whereKey($ticket->hall_ticket_id), request()->user())->exists(), 403);

        $ticket->load(['config.exam.college.university', 'student.college.university', 'student.programme', 'enrollment.semester', 'subjects.subject']);

        return view('reports.print.hall-ticket', compact('ticket'));
    }

    public function staff(Request $request): View
    {
        $staff = $this->accessScope->applyToStaff(
            Staff::with(['college', 'department', 'subjectAssignments.subject', 'leaveBalances.leaveType']),
            $request->user()
        )
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where(function ($inner) use ($term) {
                    $inner->where('employee_code', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%");
                });
            })
            ->orderBy('employee_code')
            ->paginate(25)
            ->withQueryString();

        return view('reports.staff', compact('staff'));
    }

    public function staffPrint(Staff $staff): View
    {
        abort_unless($this->accessScope->applyToStaff(Staff::whereKey($staff->staff_id), request()->user())->exists(), 403);

        $staff->load(['college.university', 'department', 'subjectAssignments.subject', 'leaveBalances.leaveType']);

        return view('reports.print.staff', compact('staff'));
    }

    public function certificates(Request $request): View
    {
        $students = $this->accessScope->applyToStudents(
            Student::with(['college.university', 'programme.department', 'category', 'enrollments.semester', 'enrollments.academicYear']),
            $request->user()
        )
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where(function ($inner) use ($term) {
                    $inner->where('enrollment_no', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%");
                });
            })
            ->orderBy('enrollment_no')
            ->paginate(25)
            ->withQueryString();

        return view('reports.certificates', compact('students'));
    }

    public function certificatePrint(Student $student, string $type): View
    {
        abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), request()->user())->exists(), 403);

        $student->load([
            'college.university',
            'programme.department',
            'category',
            'enrollments.semester',
            'enrollments.academicYear',
            'feeLedgers.academicYear',
            'feeLedgers.semester',
        ]);

        $latestEnrollment = $student->enrollments
            ->sortByDesc(fn ($enrollment) => $enrollment->enrolled_on?->format('Ymd') ?? '00000000')
            ->first();

        $feeSummary = [
            'total_amount' => (float) $student->feeLedgers->sum('total_amount'),
            'concession_amount' => (float) $student->feeLedgers->sum('concession_amount'),
            'scholarship_amount' => (float) $student->feeLedgers->sum('scholarship_amount'),
            'net_payable' => (float) $student->feeLedgers->sum('net_payable'),
            'amount_paid' => (float) $student->feeLedgers->sum('amount_paid'),
            'balance_due' => (float) $student->feeLedgers->sum('balance_due'),
        ];

        $certificateTitle = match ($type) {
            'bonafide' => 'Bonafide Certificate',
            'leaving' => 'Leaving Certificate',
            'fee' => 'Fee Certificate',
            'transfer' => 'Transfer Certificate',
            default => 'Certificate',
        };

        return view('reports.print.certificate', compact('student', 'type', 'latestEnrollment', 'feeSummary', 'certificateTitle'));
    }

    public function activity(Request $request): View
    {
        $logs = ActivityLog::with('user')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where(function ($inner) use ($term) {
                    $inner->where('route_name', 'like', "%{$term}%")
                        ->orWhere('url', 'like', "%{$term}%")
                        ->orWhere('method', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('username', 'like', "%{$term}%"));
                });
            })
            ->latest('activity_log_id')
            ->paginate(30)
            ->withQueryString();

        return view('reports.activity', compact('logs'));
    }

    public function export(string $type): StreamedResponse|Response
    {
        $permissions = [
            'students' => 'student_report.view',
            'attendance' => 'attendance_report.view',
            'staff' => 'staff_report.view',
            'receipts' => 'fee_receipt_report.view',
            'activity' => 'activity_log.view',
        ];

        if (! isset($permissions[$type])) {
            abort(404);
        }

        abort_unless(hasPermission($permissions[$type]), 403);

        $exporters = [
            'students' => fn () => [
                ['Enrollment', 'Name', 'Programme', 'College', 'Status'],
                $this->accessScope->applyToStudents(Student::with(['programme', 'college']), request()->user())->orderBy('enrollment_no')->get()->map(fn (Student $student) => [
                    $student->enrollment_no,
                    trim($student->first_name . ' ' . $student->last_name),
                    $student->programme?->name,
                    $student->college?->name,
                    $student->is_active ? 'Active' : 'Inactive',
                ]),
            ],
            'attendance' => fn () => [
                ['Enrollment', 'Student', 'Subject', 'Semester', 'Attendance %', 'Detained'],
                $this->accessScope->applyToAttendanceSummaries(AttendanceSummary::with(['student', 'subject', 'semester']), request()->user())->get()->map(fn (AttendanceSummary $summary) => [
                    $summary->student?->enrollment_no,
                    trim(($summary->student?->first_name ?? '') . ' ' . ($summary->student?->last_name ?? '')),
                    $summary->subject?->code,
                    'Sem ' . $summary->semester?->semester_no,
                    $summary->attendance_percentage,
                    $summary->is_detained ? 'Yes' : 'No',
                ]),
            ],
            'staff' => fn () => [
                ['Employee Code', 'Name', 'Department', 'Type', 'Employment', 'Status'],
                $this->accessScope->applyToStaff(Staff::with('department'), request()->user())->orderBy('employee_code')->get()->map(fn (Staff $staff) => [
                    $staff->employee_code,
                    trim($staff->first_name . ' ' . $staff->last_name),
                    $staff->department?->name,
                    $staff->staff_type,
                    $staff->employment_type,
                    $staff->is_active ? 'Active' : 'Inactive',
                ]),
            ],
            'receipts' => fn () => [
                ['Receipt No', 'Student', 'Amount', 'Mode', 'Status', 'Date'],
                $this->accessScope->applyToFeePayments(FeePayment::with('student'), request()->user())->latest('payment_id')->get()->map(fn (FeePayment $payment) => [
                    $payment->receipt_no,
                    $payment->student?->enrollment_no,
                    $payment->amount_paid,
                    $payment->payment_mode,
                    $payment->payment_status,
                    $payment->payment_date,
                ]),
            ],
            'activity' => fn () => [
                ['User', 'Method', 'Route', 'URL', 'Status', 'When'],
                ActivityLog::with('user')->latest('activity_log_id')->get()->map(fn (ActivityLog $log) => [
                    $log->user?->username,
                    $log->method,
                    $log->route_name,
                    $log->url,
                    $log->status_code,
                    $log->created_at,
                ]),
            ],
        ];

        [$headers, $rows] = $exporters[$type]();

        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, "{$type}-report.csv", ['Content-Type' => 'text/csv']);
    }
}
