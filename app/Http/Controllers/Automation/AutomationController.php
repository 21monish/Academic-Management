<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSummary;
use App\Models\HallTicket;
use App\Models\HallTicketConfig;
use App\Models\Notice;
use App\Models\NoticeAudience;
use App\Models\Result;
use App\Models\Semester;
use App\Models\Staff;
use App\Models\StaffSubjectAssignment;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeLedger;
use App\Models\StudentPromotion;
use App\Models\TimetableSlot;
use App\Services\AccessScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AutomationController extends Controller
{
    public function __construct(private readonly AccessScopeService $accessScope)
    {
    }

    public function run(Request $request, string $task): RedirectResponse
    {
        $allowedTasks = array_keys($this->tasks());
        abort_unless(in_array($task, $allowedTasks, true), 404);

        $user = $request->user();
        abort_unless($this->canRun($task), 403);

        $result = $task === 'all'
            ? $this->runAll($user)
            : [$task => $this->{$this->tasks()[$task]['method']}($user)];

        return back()->with('status', $this->statusMessage($result));
    }

    public static function dashboardTasks(): array
    {
        return [
            'all' => [
                'label' => 'Run All Automations',
                'detail' => 'Process the available one-click jobs together.',
                'permission' => ['hall_ticket.create', 'marks_entry.update', 'student_ledger.update', 'promotion.create', 'notice.update', 'attendance_summary.view', 'student_report.view', 'system_health.view', 'student_ledger.view', 'timetable_slot.create', 'dashboard.view'],
                'tone' => 'primary',
            ],
            'backup' => [
                'label' => 'Backup System',
                'detail' => 'Save database data and uploaded files.',
                'permission' => ['system_health.view', 'system_settings.update'],
                'tone' => 'primary',
            ],
            'id-cards' => [
                'label' => 'Generate ID Cards',
                'detail' => 'Create printable student ID cards with photos and department data.',
                'permission' => ['student_report.view', 'student.view'],
                'tone' => 'cyan',
            ],
            'attendance' => [
                'label' => 'Refresh Attendance',
                'detail' => 'Recalculate attendance summary percentages.',
                'permission' => ['attendance_summary.view', 'attendance_report.view'],
                'tone' => 'cyan',
            ],
            'fee-reminders' => [
                'label' => 'Send Fee Reminders',
                'detail' => 'Notify students with pending balances by notice and email.',
                'permission' => ['student_ledger.view', 'fee_report.view', 'notice.create'],
                'tone' => 'emerald',
            ],
            'timetable' => [
                'label' => 'Generate Timetable',
                'detail' => 'Create missing class slots from active staff assignments.',
                'permission' => ['timetable_slot.create', 'staff_assignment.view'],
                'tone' => 'indigo',
            ],
            'dashboard-refresh' => [
                'label' => 'Refresh Dashboard',
                'detail' => 'Recalculate summaries and clear cached dashboard data.',
                'permission' => ['dashboard.view', 'attendance_summary.view', 'fee_report.view'],
                'tone' => 'teal',
            ],
            'fees' => [
                'label' => 'Clear Fee Ledgers',
                'detail' => 'Mark fully paid ledgers as hall-ticket cleared.',
                'permission' => ['student_ledger.update', 'fee_collection.update'],
                'tone' => 'emerald',
            ],
            'hall-tickets' => [
                'label' => 'Generate Hall Tickets',
                'detail' => 'Create eligible hall tickets from active configs.',
                'permission' => 'hall_ticket.create',
                'tone' => 'indigo',
            ],
            'results' => [
                'label' => 'Publish Results',
                'detail' => 'Publish completed result rows.',
                'permission' => ['marks_entry.update', 'result.view'],
                'tone' => 'amber',
            ],
            'promotions' => [
                'label' => 'Promote Students',
                'detail' => 'Create promotion records for eligible active enrollments.',
                'permission' => ['promotion.create', 'promotion.approve'],
                'tone' => 'teal',
            ],
            'notices' => [
                'label' => 'Publish Notices',
                'detail' => 'Publish draft notices whose valid date has started.',
                'permission' => ['notice.update', 'notice.approve'],
                'tone' => 'slate',
            ],
        ];
    }

    private function tasks(): array
    {
        return [
            'all' => ['method' => 'runAll'],
            'backup' => ['method' => 'backupSystem'],
            'id-cards' => ['method' => 'generateStudentIdCards'],
            'attendance' => ['method' => 'refreshAttendanceSummaries'],
            'fee-reminders' => ['method' => 'sendFeeReminders'],
            'timetable' => ['method' => 'generateTimetable'],
            'dashboard-refresh' => ['method' => 'refreshDashboard'],
            'fees' => ['method' => 'clearFeeLedgers'],
            'hall-tickets' => ['method' => 'generateHallTickets'],
            'results' => ['method' => 'publishResults'],
            'promotions' => ['method' => 'promoteStudents'],
            'notices' => ['method' => 'publishNotices'],
        ];
    }

    private function runAll($user): array
    {
        $results = [];

        foreach (['backup', 'dashboard-refresh', 'id-cards', 'attendance', 'fee-reminders', 'timetable', 'fees', 'hall-tickets', 'results', 'promotions', 'notices'] as $task) {
            if ($this->canRun($task)) {
                $results[$task] = $this->{$this->tasks()[$task]['method']}($user);
            }
        }

        abort_if(empty($results), 403);

        return $results;
    }

    private function backupSystem($user): string
    {
        $timestamp = now()->format('Ymd-His');
        $backupRoot = storage_path('app/backups');
        $workDir = "{$backupRoot}/backup-{$timestamp}";

        File::ensureDirectoryExists($workDir);
        File::put("{$workDir}/manifest.json", json_encode([
            'created_at' => now()->toIso8601String(),
            'created_by' => $user?->user_id,
            'database_connection' => config('database.default'),
            'app' => config('app.name'),
        ], JSON_PRETTY_PRINT));
        File::put("{$workDir}/database.json", json_encode($this->databaseSnapshot(), JSON_PRETTY_PRINT));

        foreach ($this->uploadPaths() as $label => $path) {
            if (File::isDirectory($path)) {
                File::copyDirectory($path, "{$workDir}/uploads/{$label}");
            }
        }

        $zipPath = "{$backupRoot}/backup-{$timestamp}.zip";

        if (class_exists(\ZipArchive::class) && $this->zipDirectory($workDir, $zipPath)) {
            File::deleteDirectory($workDir);

            return basename($zipPath);
        }

        return basename($workDir);
    }

    private function generateStudentIdCards($user): string
    {
        $students = $this->accessScope
            ->applyToStudents(Student::with(['college.university', 'programme.department', 'enrollments.semester']), $user)
            ->where('is_active', true)
            ->orderBy('enrollment_no')
            ->get();

        $directory = storage_path('app/generated/id-cards');
        $filename = 'student-id-cards-'.now()->format('Ymd-His').'.html';

        File::ensureDirectoryExists($directory);
        File::put("{$directory}/{$filename}", view('reports.print.student-id-cards', [
            'students' => $students,
            'generatedAt' => now(),
        ])->render());

        return $students->count().' ID cards in '.$filename;
    }

    private function refreshAttendanceSummaries($user): int
    {
        $groups = Attendance::query()
            ->whereHas('student', fn (Builder $student) => $this->accessScope->applyToStudents($student, $user))
            ->join('lectures', 'lectures.lecture_id', '=', 'attendances.lecture_id')
            ->join('timetable_slots', 'timetable_slots.slot_id', '=', 'lectures.slot_id')
            ->select([
                'attendances.student_id',
                'lectures.subject_id',
                'timetable_slots.semester_id',
                DB::raw('COUNT(*) as total_lectures'),
                DB::raw("SUM(CASE WHEN attendances.status IN ('Present', 'Late', 'Excused') THEN 1 ELSE 0 END) as attended_lectures"),
            ])
            ->groupBy('attendances.student_id', 'lectures.subject_id', 'timetable_slots.semester_id')
            ->get();

        $updated = 0;

        foreach ($groups as $group) {
            $total = (int) $group->total_lectures;
            $attended = (int) $group->attended_lectures;
            $percentage = $total > 0 ? round(($attended / $total) * 100, 2) : 0;

            AttendanceSummary::updateOrCreate(
                [
                    'student_id' => $group->student_id,
                    'subject_id' => $group->subject_id,
                    'semester_id' => $group->semester_id,
                ],
                [
                    'total_lectures' => $total,
                    'attended_lectures' => $attended,
                    'attendance_percentage' => $percentage,
                    'is_detained' => $percentage < 75,
                    'updated_at' => now(),
                ]
            );

            $updated++;
        }

        return $updated;
    }

    private function sendFeeReminders($user): string
    {
        $ledgers = $this->accessScope
            ->applyToFeeLedgers(StudentFeeLedger::with(['student.college', 'student.programme.department', 'semester', 'feeStructure.feeCategory']), $user)
            ->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])
            ->where(function (Builder $query) {
                $query->where('balance_due', '>', 0)
                    ->orWhereNull('balance_due');
            })
            ->get();

        $emails = 0;
        $notices = 0;

        foreach ($ledgers->groupBy(fn ($ledger) => $ledger->student?->college_id ?: 0) as $collegeId => $collegeLedgers) {
            if (! $collegeId) {
                continue;
            }

            $notice = Notice::create([
                'college_id' => $collegeId,
                'created_by' => $user?->user_id,
                'title' => 'Pending Fee Reminder - '.now()->format('d M Y'),
                'content' => $this->feeReminderNoticeContent($collegeLedgers),
                'priority' => 'High',
                'audience_type' => 'College',
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addDays(14)->toDateString(),
                'is_pinned' => true,
                'is_published' => true,
                'requires_acknowledgement' => false,
                'published_at' => now(),
            ]);

            NoticeAudience::create([
                'notice_id' => $notice->notice_id,
                'target_type' => 'Role',
                'target_id' => 0,
            ]);

            $notices++;
        }

        foreach ($ledgers as $ledger) {
            $email = $ledger->student?->email;

            if (! $email) {
                continue;
            }

            Mail::raw($this->feeReminderEmailBody($ledger), function ($message) use ($email, $ledger) {
                $message->to($email)
                    ->subject('Pending fee reminder - '.$ledger->student?->enrollment_no);
            });

            $emails++;
        }

        return $ledgers->count().' pending ledgers, '.$notices.' notices, '.$emails.' emails';
    }

    private function generateTimetable($user): int
    {
        $assignments = $this->accessScope
            ->applyToAssignments(StaffSubjectAssignment::with(['subject', 'semester']), $user)
            ->where('is_active', true)
            ->orderBy('semester_id')
            ->orderBy('assignment_id')
            ->get();

        $created = 0;
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $times = [
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'],
            ['11:15:00', '12:15:00'],
            ['13:00:00', '14:00:00'],
            ['14:00:00', '15:00:00'],
            ['15:15:00', '16:15:00'],
        ];

        foreach ($assignments as $assignment) {
            $lectureTypes = $assignment->lecture_type === 'Both'
                ? ['Theory', 'Lab']
                : [$assignment->lecture_type === 'Lab' ? 'Lab' : 'Theory'];
            $neededSlots = max(1, (int) ($assignment->subject?->theory_hours ?: $assignment->subject?->lab_hours ?: 2));

            foreach ($lectureTypes as $lectureType) {
                $existing = TimetableSlot::where('semester_id', $assignment->semester_id)
                    ->where('subject_id', $assignment->subject_id)
                    ->where('staff_id', $assignment->staff_id)
                    ->where('lecture_type', $lectureType)
                    ->count();

                for ($slotNumber = $existing; $slotNumber < $neededSlots; $slotNumber++) {
                    $slot = $this->nextAvailableSlot($assignment, $lectureType, $days, $times, $slotNumber);

                    if (! $slot) {
                        continue 2;
                    }

                    TimetableSlot::create($slot);
                    $created++;
                }
            }
        }

        return $created;
    }

    private function refreshDashboard($user): string
    {
        $attendance = $this->refreshAttendanceSummaries($user);
        $feeLedgers = $this->clearFeeLedgers($user);

        Cache::flush();

        return $attendance.' attendance summaries, '.$feeLedgers.' fee ledgers, cache cleared';
    }

    private function clearFeeLedgers($user): int
    {
        return $this->accessScope
            ->applyToFeeLedgers(StudentFeeLedger::query(), $user)
            ->where('payment_status', 'Paid')
            ->where(function (Builder $query) {
                $query->where('balance_due', '<=', 0)
                    ->orWhereNull('balance_due');
            })
            ->update([
                'is_hall_ticket_cleared' => true,
                'updated_at' => now(),
            ]);
    }

    private function generateHallTickets($user): int
    {
        $processed = 0;
        $configs = HallTicketConfig::with('exam.examSubjects')
            ->where('is_active', true)
            ->whereHas('exam', fn (Builder $exam) => $this->accessScope->applyToExams($exam, $user))
            ->get();

        DB::transaction(function () use ($configs, $user, &$processed) {
            foreach ($configs as $config) {
                $exam = $config->exam;

                if (! $exam) {
                    continue;
                }

                $enrollments = StudentEnrollment::with('student')
                    ->where('semester_id', $exam->semester_id)
                    ->where('status', 'Active')
                    ->whereHas('student', fn (Builder $student) => $this->accessScope->applyToStudents($student, $user))
                    ->get();

                foreach ($enrollments as $enrollment) {
                    $avgAttendance = AttendanceSummary::where('student_id', $enrollment->student_id)
                        ->where('semester_id', $exam->semester_id)
                        ->avg('attendance_percentage');
                    $attendanceCleared = $avgAttendance === null || $avgAttendance >= ($config->min_attendance_pct ?? 0);
                    $feesCleared = ! $config->fees_clearance_required || StudentFeeLedger::where('student_id', $enrollment->student_id)
                        ->where('semester_id', $exam->semester_id)
                        ->where('is_hall_ticket_cleared', false)
                        ->doesntExist();
                    $eligible = $attendanceCleared && $feesCleared;
                    $reason = $eligible ? null : trim(($attendanceCleared ? '' : 'Attendance below threshold. ') . ($feesCleared ? '' : 'Fees not cleared.'));

                    $ticket = HallTicket::updateOrCreate(
                        [
                            'config_id' => $config->config_id,
                            'student_id' => $enrollment->student_id,
                            'enrollment_id' => $enrollment->enrollment_id,
                        ],
                        [
                            'hall_ticket_no' => 'HT-'.$exam->exam_id.'-'.$enrollment->student?->enrollment_no,
                            'exam_type' => 'Both',
                            'status' => $eligible ? 'Generated' : 'Draft',
                            'is_eligible' => $eligible,
                            'ineligibility_reason' => $reason,
                            'attendance_cleared' => $attendanceCleared,
                            'fees_cleared' => $feesCleared,
                            'generated' => $eligible,
                            'generated_at' => $eligible ? now() : null,
                            'generated_by' => $user?->user_id,
                            'qr_code_data' => json_encode(['exam_id' => $exam->exam_id, 'student_id' => $enrollment->student_id]),
                            'barcode' => 'HT'.str_pad((string) $exam->exam_id, 4, '0', STR_PAD_LEFT).str_pad((string) $enrollment->student_id, 6, '0', STR_PAD_LEFT),
                        ]
                    );

                    foreach ($exam->examSubjects as $examSubject) {
                        $ticket->subjects()->updateOrCreate(
                            ['subject_id' => $examSubject->subject_id],
                            [
                                'subject_type' => 'Both',
                                'theory_exam_date' => $examSubject->exam_date,
                                'theory_exam_time' => $examSubject->exam_time,
                                'is_eligible' => $eligible,
                                'ineligibility_reason' => $reason,
                            ]
                        );
                    }

                    $processed++;
                }
            }
        });

        return $processed;
    }

    private function publishResults($user): int
    {
        return $this->accessScope
            ->applyToResults(Result::query(), $user)
            ->where('is_published', false)
            ->whereNotNull('result_status')
            ->update([
                'is_published' => true,
                'declared_at' => now(),
            ]);
    }

    private function promoteStudents($user): int
    {
        $staffId = $this->staffIdFor($user);
        $promoted = 0;
        $enrollments = StudentEnrollment::with(['student', 'semester'])
            ->where('status', 'Active')
            ->whereHas('student', fn (Builder $student) => $this->accessScope->applyToStudents($student, $user))
            ->get();

        DB::transaction(function () use ($enrollments, $staffId, &$promoted) {
            foreach ($enrollments as $enrollment) {
                $currentSemester = $enrollment->semester;
                $nextSemester = $currentSemester ? Semester::query()
                    ->where('programme_id', $currentSemester->programme_id)
                    ->where('semester_no', $currentSemester->semester_no + 1)
                    ->first() : null;

                if (! $nextSemester) {
                    continue;
                }

                $failedSubjects = Result::where('student_id', $enrollment->student_id)
                    ->where('enrollment_id', $enrollment->enrollment_id)
                    ->whereIn('result_status', ['Fail', 'ATKT'])
                    ->count();
                $attendance = AttendanceSummary::where('student_id', $enrollment->student_id)
                    ->where('semester_id', $enrollment->semester_id)
                    ->avg('attendance_percentage');
                $status = ($attendance === null || $attendance >= 75) && $failedSubjects <= 2 ? 'Promoted' : 'Detained';

                StudentPromotion::updateOrCreate(
                    [
                        'student_id' => $enrollment->student_id,
                        'from_semester_id' => $enrollment->semester_id,
                        'to_semester_id' => $nextSemester->semester_id,
                        'academic_year_id' => $enrollment->academic_year_id,
                    ],
                    [
                        'promotion_status' => $status,
                        'backlogs_at_promotion' => $failedSubjects,
                        'attendance_pct' => $attendance ? round((float) $attendance, 2) : null,
                        'is_manual_override' => false,
                        'approved_by' => $staffId,
                        'remarks' => 'Generated by one-click automation.',
                        'promoted_on' => now(),
                    ]
                );

                if ($status === 'Promoted') {
                    StudentEnrollment::firstOrCreate(
                        [
                            'student_id' => $enrollment->student_id,
                            'semester_id' => $nextSemester->semester_id,
                        ],
                        [
                            'academic_year_id' => $enrollment->academic_year_id,
                            'enrolled_on' => now()->toDateString(),
                            'status' => 'Active',
                        ]
                    );
                }

                $promoted++;
            }
        });

        return $promoted;
    }

    private function publishNotices($user): int
    {
        return Notice::query()
            ->where('created_by', $user?->user_id)
            ->where('is_published', false)
            ->where(function (Builder $query) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now()->toDateString());
            })
            ->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
    }

    private function feeReminderNoticeContent($ledgers): string
    {
        $total = (float) $ledgers->sum(fn ($ledger) => $ledger->balance_due ?? max(($ledger->net_payable ?? $ledger->total_amount ?? 0) - ($ledger->amount_paid ?? 0), 0));

        return "Pending fee reminder generated on ".now()->format('d M Y').".\n\n"
            .'Students with pending ledgers: '.$ledgers->pluck('student_id')->unique()->count()."\n"
            .'Total pending amount: INR '.number_format($total, 2)."\n\n"
            .'Please clear pending fees or contact the accounts office if already paid.';
    }

    private function feeReminderEmailBody(StudentFeeLedger $ledger): string
    {
        $student = $ledger->student;
        $balance = $ledger->balance_due ?? max(($ledger->net_payable ?? $ledger->total_amount ?? 0) - ($ledger->amount_paid ?? 0), 0);

        return "Dear {$student?->first_name} {$student?->last_name},\n\n"
            ."This is a reminder that your fee payment is pending.\n\n"
            .'Enrollment No: '.($student?->enrollment_no ?? '-')."\n"
            .'Programme: '.($student?->programme?->name ?? '-')."\n"
            .'Semester: '.($ledger->semester?->semester_no ? 'Sem '.$ledger->semester->semester_no : '-')."\n"
            .'Fee: '.($ledger->feeStructure?->feeCategory?->name ?? 'Fee')."\n"
            .'Pending Amount: INR '.number_format((float) $balance, 2)."\n\n"
            ."Please complete payment or contact the accounts office if this has already been settled.\n\n"
            .config('app.name');
    }

    private function nextAvailableSlot(StaffSubjectAssignment $assignment, string $lectureType, array $days, array $times, int $offset): ?array
    {
        $attempts = count($days) * count($times);

        for ($i = 0; $i < $attempts; $i++) {
            $index = ($offset + $i) % $attempts;
            $day = $days[intdiv($index, count($times))];
            [$start, $end] = $times[$index % count($times)];

            $conflict = TimetableSlot::where('day_of_week', $day)
                ->where('start_time', $start)
                ->where(function (Builder $query) use ($assignment) {
                    $query->where(function (Builder $staff) use ($assignment) {
                        $staff->where('staff_id', $assignment->staff_id);
                    })->orWhere(function (Builder $semester) use ($assignment) {
                        $semester->where('semester_id', $assignment->semester_id);
                    });
                })
                ->exists();

            if ($conflict) {
                continue;
            }

            return [
                'college_id' => $assignment->college_id,
                'semester_id' => $assignment->semester_id,
                'subject_id' => $assignment->subject_id,
                'staff_id' => $assignment->staff_id,
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end,
                'lecture_type' => $lectureType,
                'room_no' => $lectureType === 'Lab' ? 'Lab' : null,
                'academic_year' => $assignment->academic_year,
                'is_active' => true,
            ];
        }

        return null;
    }

    private function databaseSnapshot(): array
    {
        $tables = [];

        foreach ($this->tableNames() as $table) {
            $tables[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }

        return [
            'tables' => $tables,
        ];
    }

    private function tableNames(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("select name from sqlite_master where type = 'table' and name not like 'sqlite_%'"))
                ->pluck('name')
                ->all();
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return collect(DB::select('SHOW TABLES'))
                ->map(fn ($row) => array_values((array) $row)[0] ?? null)
                ->filter()
                ->values()
                ->all();
        }

        return collect(DB::select("select table_name from information_schema.tables where table_schema = 'public'"))
            ->pluck('table_name')
            ->all();
    }

    private function uploadPaths(): array
    {
        return [
            'public-photos' => public_path('uploads/photos'),
            'public-logos' => public_path('uploads/logos'),
            'public-notices' => public_path('uploads/notices'),
            'storage-documents' => storage_path('app/uploads/documents'),
            'storage-notices' => storage_path('app/uploads/notices'),
        ];
    }

    private function zipDirectory(string $source, string $destination): bool
    {
        $zip = new \ZipArchive();

        if ($zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach (File::allFiles($source) as $file) {
            $zip->addFile($file->getRealPath(), $file->getRelativePathname());
        }

        return $zip->close();
    }

    private function staffIdFor($user): ?int
    {
        if (! $user) {
            return null;
        }

        return Staff::query()
            ->where('email', $user->email)
            ->value('staff_id');
    }

    private function canRun(string $task): bool
    {
        $taskConfig = self::dashboardTasks()[$task] ?? null;

        if (! $taskConfig) {
            return false;
        }

        foreach ((array) $taskConfig['permission'] as $permission) {
            if (function_exists('hasPermission') && hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    private function statusMessage(array $results): string
    {
        $labels = [
            'backup' => 'system backup',
            'id-cards' => 'student ID cards',
            'attendance' => 'attendance summaries',
            'fee-reminders' => 'fee reminders',
            'timetable' => 'timetable slots',
            'dashboard-refresh' => 'dashboard refresh',
            'fees' => 'fee ledgers',
            'hall-tickets' => 'hall tickets',
            'results' => 'results',
            'promotions' => 'promotion records',
            'notices' => 'notices',
        ];

        return collect($results)
            ->map(fn (int|string $count, string $task) => is_numeric($count)
                ? number_format((float) $count).' '.$labels[$task]
                : $labels[$task].': '.$count)
            ->join(', ', ' and ')
            ?: Str::headline('automation complete');
    }
}
