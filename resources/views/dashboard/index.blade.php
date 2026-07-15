<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">
                    {{ $roleName ?? 'User' }} Dashboard
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    A permission-aware workspace for your campus operations.
                </p>
            </div>

            <div class="rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-2 text-right">
                <p class="text-xs font-bold uppercase text-cyan-700">Signed in as</p>
                <p class="text-sm font-semibold text-cyan-950">{{ auth()->user()?->name }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $stats = $stats ?? [];
        $analytics = $analytics ?? [];
        $pageSections = $pageSections ?? [];
        $dashboardData = $dashboardData ?? [];
        $recentActivity = $recentActivity ?? collect();

        $canSeePermission = function ($permissions = null): bool {
            if (empty($permissions)) {
                return true;
            }

            foreach ((array) $permissions as $permission) {
                if (function_exists('hasPermission') && hasPermission($permission)) {
                    return true;
                }
            }

            return false;
        };

        $routePermissions = collect($pageSections)
            ->flatten(1)
            ->filter(fn ($item) => ! empty($item['route']))
            ->mapWithKeys(fn ($item) => [$item['route'] => $item['permission'] ?? null]);

        $quickLinks = collect($dashboardData['quickLinks'] ?? [])
            ->filter(function ($link) use ($routePermissions, $canSeePermission) {
                if (empty($link['route']) || ! Route::has($link['route'])) {
                    return false;
                }

                $permissions = $routePermissions->get($link['route']);

                return $permissions === null || $canSeePermission($permissions);
            })
            ->values();

        $metricCatalog = [
            'universities' => ['label' => 'Universities', 'format' => 'number', 'tone' => 'cyan'],
            'colleges' => ['label' => 'Colleges', 'format' => 'number', 'tone' => 'cyan'],
            'departments' => ['label' => 'Departments', 'format' => 'number', 'tone' => 'teal'],
            'staff' => ['label' => 'Staff', 'format' => 'number', 'tone' => 'slate'],
            'departmentStaff' => ['label' => 'Department Staff', 'format' => 'number', 'tone' => 'slate'],
            'students' => ['label' => 'Students', 'format' => 'number', 'tone' => 'emerald'],
            'users' => ['label' => 'Users', 'format' => 'number', 'tone' => 'indigo'],
            'exams' => ['label' => 'Exams', 'format' => 'number', 'tone' => 'amber'],
            'notices' => ['label' => 'Published Notices', 'format' => 'number', 'tone' => 'cyan'],
            'publishedNotices' => ['label' => 'Published Notices', 'format' => 'number', 'tone' => 'cyan'],
            'hallTickets' => ['label' => 'Hall Tickets', 'format' => 'number', 'tone' => 'indigo'],
            'attendanceAverage' => ['label' => 'Attendance Avg', 'format' => 'percent', 'tone' => 'emerald'],
            'publishedResults' => ['label' => 'Published Results', 'format' => 'number', 'tone' => 'teal'],
            'feeBalance' => ['label' => 'Fee Balance', 'format' => 'money', 'tone' => 'amber'],
            'feeCollected' => ['label' => 'Fee Collected', 'format' => 'money', 'tone' => 'emerald'],
            'assignedSubjects' => ['label' => 'Assigned Subjects', 'format' => 'number', 'tone' => 'cyan'],
            'lectures' => ['label' => 'Lectures', 'format' => 'number', 'tone' => 'slate'],
            'attendanceMarked' => ['label' => 'Attendance Marked', 'format' => 'number', 'tone' => 'emerald'],
            'resultsEntered' => ['label' => 'Results Entered', 'format' => 'number', 'tone' => 'teal'],
            'subjects' => ['label' => 'Subjects', 'format' => 'number', 'tone' => 'indigo'],
        ];

        $formatMetric = function ($value, string $format = 'number'): string {
            return match ($format) {
                'money' => 'INR '.number_format((float) $value, 2),
                'percent' => number_format((float) $value, 1).'%',
                default => number_format((float) $value),
            };
        };

        $toneClasses = [
            'cyan' => 'border-cyan-100 bg-cyan-50 text-cyan-900',
            'teal' => 'border-teal-100 bg-teal-50 text-teal-900',
            'emerald' => 'border-emerald-100 bg-emerald-50 text-emerald-900',
            'amber' => 'border-amber-100 bg-amber-50 text-amber-900',
            'indigo' => 'border-indigo-100 bg-indigo-50 text-indigo-900',
            'slate' => 'border-slate-200 bg-white text-slate-900',
        ];

        $summaryCards = collect($stats)
            ->map(function ($value, $key) use ($metricCatalog) {
                $meta = $metricCatalog[$key] ?? [
                    'label' => \Illuminate\Support\Str::of($key)->headline()->toString(),
                    'format' => 'number',
                    'tone' => 'slate',
                ];

                return $meta + ['value' => $value];
            })
            ->values();

        $palette = [
            'bg-cyan-600' => '#0891b2',
            'bg-teal-600' => '#0d9488',
            'bg-slate-700' => '#334155',
            'bg-emerald-600' => '#059669',
            'bg-amber-500' => '#f59e0b',
            'bg-indigo-600' => '#4f46e5',
            'bg-red-500' => '#ef4444',
            'bg-slate-400' => '#94a3b8',
            'bg-slate-500' => '#64748b',
            'bg-violet-600' => '#7c3aed',
        ];

        $chartColor = fn ($color) => is_string($color) && str_starts_with($color, '#')
            ? $color
            : ($palette[$color] ?? '#0891b2');

        $analyticsCards = collect($analytics['cards'] ?? [])
            ->filter(fn ($card) => $canSeePermission($card['permission'] ?? null))
            ->values();

        $analyticsCharts = collect($analytics['charts'] ?? [])
            ->map(function ($chart) use ($canSeePermission) {
                $items = collect($chart['items'] ?? [])
                    ->filter(fn ($item) => $canSeePermission($item['permission'] ?? ($chart['permission'] ?? null)))
                    ->values();

                $chart['items'] = $items;

                return $chart;
            })
            ->filter(fn ($chart) => $chart['items']->isNotEmpty() && $canSeePermission($chart['permission'] ?? null))
            ->values();

        $notices = collect($dashboardData['notices'] ?? []);
        $automationTasks = collect(\App\Http\Controllers\Automation\AutomationController::dashboardTasks())
            ->filter(fn ($task) => $canSeePermission($task['permission'] ?? null))
            ->map(fn ($task, $key) => $task + ['key' => $key])
            ->values();

        $automationToneClasses = [
            'primary' => 'border-slate-900 bg-slate-900 text-white hover:bg-slate-800',
            'cyan' => 'border-cyan-200 bg-cyan-50 text-cyan-900 hover:bg-cyan-100',
            'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-900 hover:bg-emerald-100',
            'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-900 hover:bg-indigo-100',
            'amber' => 'border-amber-200 bg-amber-50 text-amber-900 hover:bg-amber-100',
            'teal' => 'border-teal-200 bg-teal-50 text-teal-900 hover:bg-teal-100',
            'slate' => 'border-slate-200 bg-slate-50 text-slate-900 hover:bg-slate-100',
        ];

        $visibleSections = collect($pageSections)
            ->map(function ($items) use ($canSeePermission) {
                return collect($items)
                    ->filter(fn ($item) => ! empty($item['route']) && Route::has($item['route']) && $canSeePermission($item['permission'] ?? null))
                    ->values();
            })
            ->filter(fn ($items) => $items->isNotEmpty());

        $visibleModuleCount = $visibleSections->count();
        $visiblePageCount = $visibleSections->sum(fn ($items) => $items->count());
        $operationsReadiness = $analyticsCards->isNotEmpty()
            ? round((float) $analyticsCards->avg(fn ($card) => (float) ($card['value'] ?? 0)), 1)
            : null;

        $attentionItems = collect($analyticsCards)
            ->filter(fn ($card) => (float) ($card['value'] ?? 0) < 75)
            ->map(fn ($card) => [
                'label' => $card['label'] ?? 'Metric',
                'value' => number_format((float) ($card['value'] ?? 0), 1).'%',
                'detail' => $card['detail'] ?? 'Needs review',
            ])
            ->values();

        if ($attentionItems->isEmpty() && ($roleName ?? null) !== 'Student') {
            $attentionItems = collect([
                ['label' => 'Operations', 'value' => 'OK', 'detail' => 'No low KPI areas detected in visible modules.'],
            ]);
        }
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if(($roleName ?? null) === 'Student')
            @php
                $studentAttendance = collect($dashboardData['attendance'] ?? []);
                $studentResults = collect($dashboardData['results'] ?? []);
                $studentFees = collect($dashboardData['fees'] ?? []);
                $activeEnrollment = $student?->enrollments?->firstWhere('status', 'Active') ?? $student?->enrollments?->first();
                $studentPhoto = $student?->photo_url ? asset($student->photo_url) : null;
                $studentInitials = strtoupper(substr((string) $student?->first_name, 0, 1).substr((string) $student?->last_name, 0, 1));
                $studentName = trim((string) $student?->first_name.' '.(string) $student?->last_name) ?: auth()->user()?->name;
                $programmeName = $student?->programme?->name ?? $student?->programme?->programme_name ?? '-';
                $currentSemester = $activeEnrollment?->semester?->semester_no ? 'Sem '.$activeEnrollment->semester->semester_no : '-';
                $currentAcademicYear = $activeEnrollment?->academicYear?->label ?? '-';
                $attendanceAverage = (float) ($stats['attendanceAverage'] ?? 0);
                $lowestAttendance = $studentAttendance->min(fn ($attendance) => (float) ($attendance->attendance_percentage ?? 0));
                $feeBalance = (float) ($stats['feeBalance'] ?? $studentFees->sum('balance_due'));
                $feePaid = (float) $studentFees->sum('amount_paid');
                $profileFields = collect([
                    $student?->email,
                    $student?->phone,
                    $student?->address,
                    $student?->guardian_name,
                    $student?->guardian_phone,
                    $student?->photo_url,
                ]);
                $profileComplete = $profileFields->count() > 0
                    ? round(($profileFields->filter()->count() / $profileFields->count()) * 100)
                    : 0;
            @endphp

            @if(! $student)
                <section class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-5 text-amber-950 shadow-sm">
                    <p class="text-sm font-black">Student record not linked</p>
                    <p class="mt-1 text-sm font-semibold text-amber-800">
                        Your login is active, but no matching student profile was found for this account. Please contact the office to link your enrollment record.
                    </p>
                </section>
            @endif

            <section class="grid gap-4 lg:grid-cols-[1fr_360px]">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                            @if($studentPhoto)
                                <img src="{{ $studentPhoto }}" alt="{{ $student?->first_name }} {{ $student?->last_name }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-2xl font-black text-cyan-800">{{ $studentInitials ?: 'ST' }}</span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Student Workspace</p>
                            <h3 class="mt-2 text-2xl font-black text-slate-950">
                                {{ $studentName }}
                            </h3>
                            <div class="mt-3 grid gap-2 text-sm font-semibold text-slate-600 sm:grid-cols-2">
                                <p><span class="text-slate-400">Enrollment:</span> {{ $student?->enrollment_no ?? '-' }}</p>
                                <p><span class="text-slate-400">Programme:</span> {{ $programmeName }}</p>
                                <p><span class="text-slate-400">Semester:</span> {{ $currentSemester }}</p>
                                <p><span class="text-slate-400">Academic Year:</span> {{ $currentAcademicYear }}</p>
                                <p><span class="text-slate-400">College:</span> {{ $student?->college?->name ?? '-' }}</p>
                                <p><span class="text-slate-400">Status:</span> {{ $student?->is_active ? 'Active' : 'Inactive' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($summaryCards->isNotEmpty())
                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($summaryCards as $card)
                                <div class="rounded-lg border p-4 {{ $toneClasses[$card['tone']] ?? $toneClasses['slate'] }}">
                                    <p class="text-xs font-bold uppercase tracking-wide opacity-70">{{ $card['label'] }}</p>
                                    <p class="mt-2 text-2xl font-black">{{ $formatMetric($card['value'], $card['format']) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-5 grid gap-3 md:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Profile</p>
                            <p class="mt-2 text-2xl font-black text-slate-950">{{ $profileComplete }}%</p>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                                <div class="h-full rounded-full bg-cyan-600" style="width: {{ $profileComplete }}%;"></div>
                            </div>
                        </div>
                        <div class="rounded-lg border {{ $attendanceAverage < 75 ? 'border-red-100 bg-red-50 text-red-900' : 'border-emerald-100 bg-emerald-50 text-emerald-900' }} p-4">
                            <p class="text-xs font-bold uppercase tracking-wide opacity-75">Attendance Health</p>
                            <p class="mt-2 text-2xl font-black">{{ number_format($attendanceAverage, 1) }}%</p>
                            <p class="mt-1 text-xs font-semibold opacity-80">
                                {{ $lowestAttendance !== null ? 'Lowest subject: '.number_format((float) $lowestAttendance, 1).'%' : 'No subject summaries yet' }}
                            </p>
                        </div>
                        <div class="rounded-lg border {{ $feeBalance > 0 ? 'border-amber-100 bg-amber-50 text-amber-900' : 'border-emerald-100 bg-emerald-50 text-emerald-900' }} p-4">
                            <p class="text-xs font-bold uppercase tracking-wide opacity-75">Fee Balance</p>
                            <p class="mt-2 text-2xl font-black">INR {{ number_format($feeBalance, 2) }}</p>
                            <p class="mt-1 text-xs font-semibold opacity-80">Recent paid: INR {{ number_format($feePaid, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-950">My Shortcuts</h3>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Student actions</p>
                        </div>
                        <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">{{ $quickLinks->count() }}</span>
                    </div>

                    <div class="mt-4 grid gap-2">
                        @forelse($quickLinks as $link)
                            <a href="{{ route($link['route']) }}" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition-colors hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-800">
                                <span>{{ $link['label'] }}</span>
                                <span aria-hidden="true">-></span>
                            </a>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                                No shortcuts are available.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">
                <div class="space-y-6">
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-950">Attendance</h3>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    {{ $attendanceAverage < 75 ? 'Action needed to reach the 75% threshold' : 'Lowest subject attendance first' }}
                                </p>
                            </div>
                            <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $studentAttendance->count() }} subjects</span>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse($studentAttendance as $attendance)
                                @php
                                    $attendancePct = max(0, min(100, (float) ($attendance->attendance_percentage ?? 0)));
                                    $attendanceColor = $attendancePct < 75 ? '#ef4444' : ($attendancePct < 90 ? '#f59e0b' : '#059669');
                                @endphp
                                <div>
                                    <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                        <span class="font-semibold text-slate-700">{{ $attendance->subject?->code }} - {{ $attendance->subject?->name }}</span>
                                        <span class="font-black text-slate-900">{{ number_format($attendancePct, 1) }}%</span>
                                    </div>
                                    <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full" style="width: {{ $attendancePct }}%; background-color: {{ $attendanceColor }};"></div>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ $attendance->attended_lectures }} attended of {{ $attendance->total_lectures }} lectures
                                        @if($attendancePct < 75)
                                            <span class="font-black text-red-600">- below requirement</span>
                                        @endif
                                    </p>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                                    Attendance summaries are not available yet.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-950">Recent Results</h3>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Published result records</p>
                            </div>
                            <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $studentResults->count() }} records</span>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2">Subject</th>
                                        <th class="px-3 py-2">Marks</th>
                                        <th class="px-3 py-2">Grade</th>
                                        <th class="px-3 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse($studentResults as $result)
                                        <tr>
                                            <td class="px-3 py-2">
                                                <span class="block font-semibold text-slate-800">{{ $result->examSubject?->subject?->code ?? '-' }}</span>
                                                <span class="block text-xs font-semibold text-slate-400">{{ $result->examSubject?->exam?->exam_name ?? 'Exam' }}</span>
                                            </td>
                                            <td class="px-3 py-2 text-slate-600">{{ $result->total_marks !== null ? number_format($result->total_marks, 1) : '-' }}</td>
                                            <td class="px-3 py-2 font-bold text-slate-800">{{ $result->grade ?? '-' }}</td>
                                            <td class="px-3 py-2">
                                                @php($resultTone = $result->result_status === 'Pass' ? 'bg-emerald-100 text-emerald-800' : (($result->result_status === 'Fail') ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-700'))
                                                <span class="rounded-md px-2 py-1 text-xs font-bold {{ $resultTone }}">{{ $result->result_status ?? '-' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-3 py-4 text-sm font-semibold text-slate-500">No published results found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-950">Fee Status</h3>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Latest ledgers and balances</p>
                            </div>
                            <span class="rounded-md {{ $feeBalance > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }} px-2 py-1 text-xs font-bold">
                                {{ $feeBalance > 0 ? 'Due' : 'Clear' }}
                            </span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @forelse($studentFees as $fee)
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ $fee->feeStructure?->feeCategory?->name ?? 'Fee' }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $fee->semester?->semester_no ? 'Sem '.$fee->semester->semester_no : 'Semester not set' }}</p>
                                        </div>
                                        <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-700">{{ $fee->payment_status }}</span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-semibold text-slate-600">
                                        <p>Paid<br><span class="text-sm font-black text-emerald-700">INR {{ number_format((float) $fee->amount_paid, 2) }}</span></p>
                                        <p>Balance<br><span class="text-sm font-black text-amber-700">INR {{ number_format((float) $fee->balance_due, 2) }}</span></p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                                    No fee ledgers found.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-950">Notices</h3>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Latest announcements</p>
                            </div>
                            @if(Route::has('notices.index') && $canSeePermission('notice.view'))
                                <a href="{{ route('notices.index') }}" class="text-xs font-bold text-cyan-700 hover:text-cyan-900">View all</a>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse($notices as $notice)
                                @php
                                    $noticeDate = $notice->published_at ?? $notice->created_at ?? null;
                                    $noticeDate = $noticeDate ? \Illuminate\Support\Carbon::parse($noticeDate)->format('d M Y') : 'Not dated';
                                @endphp
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-sm font-bold text-slate-900">{{ $notice->title }}</p>
                                        <span class="shrink-0 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600">{{ $notice->priority }}</span>
                                    </div>
                                    <p class="mt-2 text-xs font-semibold text-slate-500">{{ $notice->category?->name ?? 'General' }} - {{ $noticeDate }}</p>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                                    No published notices found.
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </section>
        @else
        <section class="grid gap-4 lg:grid-cols-[1fr_360px]">
            <div class="dashboard-hero rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Campus Snapshot</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-950">
                            Welcome back, {{ auth()->user()?->name ?? 'User' }}
                        </h3>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Your dashboard now adapts to role context and direct permissions, so the numbers and links here stay aligned with what you can actually use.
                        </p>
                    </div>

                    <div class="grid gap-2 text-right sm:min-w-44">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <p class="text-xs font-bold uppercase text-slate-400">Workspace</p>
                            <p class="mt-1 text-sm font-black text-slate-900">{{ now()->format('d M Y') }}</p>
                        </div>
                        <div class="rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-2">
                            <p class="text-xs font-bold uppercase text-cyan-700">Access Surface</p>
                            <p class="mt-1 text-sm font-black text-cyan-950">{{ $visiblePageCount }} pages</p>
                        </div>
                    </div>
                </div>

                @if($summaryCards->isNotEmpty())
                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($summaryCards as $card)
                            <div class="rounded-lg border p-4 transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-sm {{ $toneClasses[$card['tone']] ?? $toneClasses['slate'] }}">
                                <p class="text-xs font-bold uppercase tracking-wide opacity-70">{{ $card['label'] }}</p>
                                <p class="mt-2 text-2xl font-black">
                                    {{ $formatMetric($card['value'], $card['format']) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-950">Quick Links</h3>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Common actions for your role</p>
                    </div>
                    <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                        {{ $quickLinks->count() }}
                    </span>
                </div>

                @if($quickLinks->isEmpty())
                    <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                        No quick links are available for your current permissions.
                    </div>
                @else
                    <div class="mt-4 grid gap-2">
                        @foreach($quickLinks as $link)
                            <a href="{{ route($link['route']) }}" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition-all duration-200 ease-out hover:translate-x-0.5 hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-800">
                                <span>{{ $link['label'] }}</span>
                                <span aria-hidden="true">-></span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="dashboard-command mt-6 grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase text-cyan-700">Command Center</p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">Operational Readiness</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">A quick read on the modules and KPIs available to this login.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-right">
                        <p class="text-xs font-bold uppercase text-slate-400">Visible Modules</p>
                        <p class="mt-1 text-lg font-black text-slate-950">{{ $visibleModuleCount }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-[180px_1fr]">
                    <div class="grid place-items-center rounded-lg bg-slate-50 p-4">
                        @php
                            $readiness = $operationsReadiness !== null ? max(0, min(100, $operationsReadiness)) : 0;
                            $readinessDegrees = round($readiness * 3.6, 2);
                        @endphp
                        <div class="grid h-32 w-32 place-items-center rounded-full shadow-inner"
                             style="background: conic-gradient(var(--theme-primary) 0deg {{ $readinessDegrees }}deg, #e2e8f0 {{ $readinessDegrees }}deg 360deg);">
                            <div class="grid h-20 w-20 place-items-center rounded-full bg-white text-center shadow-sm">
                                <span>
                                    <span class="block text-xl font-black text-slate-950">{{ $operationsReadiness !== null ? number_format($readiness, 0).'%' : '-' }}</span>
                                    <span class="block text-[10px] font-black uppercase text-slate-400">Ready</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Pages</p>
                            <p class="mt-2 text-2xl font-black text-slate-950">{{ $visiblePageCount }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Permission-visible routes</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Shortcuts</p>
                            <p class="mt-2 text-2xl font-black text-slate-950">{{ $quickLinks->count() }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Role quick actions</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Alerts</p>
                            <p class="mt-2 text-2xl font-black {{ $attentionItems->first()['value'] === 'OK' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $attentionItems->first()['value'] === 'OK' ? 0 : $attentionItems->count() }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">KPI areas to review</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase text-cyan-700">Needs Attention</p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">Priority Watchlist</h3>
                    </div>
                    <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">{{ $attentionItems->count() }}</span>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach($attentionItems->take(4) as $item)
                        <div class="rounded-lg border {{ $item['value'] === 'OK' ? 'border-emerald-100 bg-emerald-50' : 'border-amber-100 bg-amber-50' }} p-3">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-black {{ $item['value'] === 'OK' ? 'text-emerald-900' : 'text-amber-950' }}">{{ $item['label'] }}</p>
                                <span class="rounded-md bg-white/80 px-2 py-1 text-xs font-black {{ $item['value'] === 'OK' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $item['value'] }}</span>
                            </div>
                            <p class="mt-2 text-xs font-semibold {{ $item['value'] === 'OK' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $item['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @if($visibleSections->isNotEmpty())
            <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase text-cyan-700">Module Launchpad</p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">Open Work Areas</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Grouped by the same permissions that control your sidebar.</p>
                    </div>
                    <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $visibleModuleCount }} groups</span>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($visibleSections as $section => $items)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h4 class="text-sm font-black text-slate-950">{{ $section }}</h4>
                                <span class="rounded-md bg-white px-2 py-1 text-xs font-black text-slate-500">{{ $items->count() }}</span>
                            </div>
                            <div class="grid gap-2">
                                @foreach($items->take(5) as $item)
                                    <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm hover:text-cyan-800">
                                        <span class="truncate">{{ $item['label'] }}</span>
                                        <span aria-hidden="true">-></span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($automationTasks->isNotEmpty())
            <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">One-Click Automation</p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">Run campus work in one click</h3>
                    </div>
                    <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                        {{ $automationTasks->count() }} available
                    </span>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @foreach($automationTasks as $task)
                        <form method="POST" action="{{ route('automations.run', $task['key']) }}" class="{{ $task['key'] === 'all' ? 'md:col-span-2 xl:col-span-4' : '' }}">
                            @csrf
                            <button type="submit"
                                    class="flex h-full w-full items-center justify-between gap-4 rounded-lg border px-4 py-3 text-left shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md {{ $automationToneClasses[$task['tone'] ?? 'slate'] ?? $automationToneClasses['slate'] }}"
                                    onclick="return confirm('Run {{ $task['label'] }} now?');">
                                <span>
                                    <span class="block text-sm font-black">{{ $task['label'] }}</span>
                                    <span class="mt-1 block text-xs font-semibold opacity-75">{{ $task['detail'] }}</span>
                                </span>
                                <span class="shrink-0 text-lg font-black" aria-hidden="true">-></span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>
        @endif

        @if($analyticsCards->isNotEmpty())
            <section class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($analyticsCards as $card)
                    @php
                        $percent = max(0, min(100, (float) ($card['value'] ?? 0)));
                        $degrees = round($percent * 3.6, 2);
                        $color = $card['color'] ?? '#0891b2';
                    @endphp

                    <div class="flex items-center gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-md">
                        <div class="grid h-20 w-20 shrink-0 place-items-center rounded-full"
                             style="background: conic-gradient({{ $color }} 0deg {{ $degrees }}deg, #e2e8f0 {{ $degrees }}deg 360deg);">
                            <div class="grid h-14 w-14 place-items-center rounded-full bg-white">
                                <span class="text-sm font-black text-slate-900">{{ number_format($percent, 0) }}%</span>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">{{ $card['label'] ?? 'Metric' }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $card['detail'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">
            @if($analyticsCharts->isNotEmpty())
                <div class="grid grid-cols-1 gap-4">
                    @foreach($analyticsCharts as $chart)
                        @php
                            $items = collect($chart['items'] ?? []);
                            $total = (float) $items->sum('value');
                            $maxValue = max((float) ($items->max('value') ?? 0), 1);
                            $format = $chart['format'] ?? 'number';
                            $nonZeroItems = $items->filter(fn ($item) => (float) ($item['value'] ?? 0) > 0)->values();
                            $leader = $nonZeroItems->sortByDesc(fn ($item) => (float) ($item['value'] ?? 0))->first();
                            $leaderShare = $leader && $total > 0 ? round(((float) $leader['value'] / $total) * 100, 1) : 0;
                            $segments = [];
                            $cursor = 0.0;

                            foreach ($nonZeroItems as $item) {
                                $value = (float) ($item['value'] ?? 0);
                                $share = $total > 0 ? round(($value / $total) * 100, 2) : 0;
                                $next = min(100, $cursor + $share);
                                $segments[] = $chartColor($item['color'] ?? '#0891b2').' '.$cursor.'% '.$next.'%';
                                $cursor = $next;
                            }

                            $donutGradient = $segments
                                ? 'conic-gradient('.implode(', ', $segments).', #e2e8f0 '.$cursor.'% 100%)'
                                : 'conic-gradient(#e2e8f0 0% 100%)';
                        @endphp

                        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:shadow-md">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase text-cyan-700">Analytics</p>
                                    <h4 class="mt-1 text-base font-black text-slate-950">{{ $chart['title'] ?? 'Analysis' }}</h4>
                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">{{ $chart['subtitle'] ?? '' }}</p>
                                </div>

                                <div class="text-right">
                                    <p class="text-xs font-bold uppercase text-slate-400">Total</p>
                                    <p class="text-lg font-black text-slate-950">{{ $formatMetric($total, $format) }}</p>
                                </div>
                            </div>

                            @if($total <= 0)
                                <div class="mt-5 grid place-items-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                                    <div class="grid h-12 w-12 place-items-center rounded-full bg-white text-slate-400 shadow-sm">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h16M7 16V9m5 7V5m5 11v-4"/>
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-bold text-slate-700">No chart data yet</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">Once records are added, this chart will update automatically.</p>
                                </div>
                            @else
                                <div class="mt-5 grid gap-5 lg:grid-cols-[168px_1fr]">
                                    <div class="flex flex-col items-center justify-center rounded-lg bg-slate-50 p-4">
                                        <div class="grid h-32 w-32 place-items-center rounded-full shadow-inner" style="background: {{ $donutGradient }};">
                                            <div class="grid h-20 w-20 place-items-center rounded-full bg-white text-center shadow-sm">
                                                <span>
                                                    <span class="block text-lg font-black text-slate-950">{{ number_format($leaderShare, 0) }}%</span>
                                                    <span class="block text-[10px] font-black uppercase text-slate-400">Top</span>
                                                </span>
                                            </div>
                                        </div>
                                        <p class="mt-3 max-w-36 text-center text-xs font-bold text-slate-600">
                                            {{ $leader['label'] ?? 'No leader' }}
                                        </p>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach($items as $item)
                                            @php
                                                $value = (float) ($item['value'] ?? 0);
                                                $width = $value > 0 ? max(5, round(($value / $maxValue) * 100, 2)) : 0;
                                                $share = $total > 0 ? round(($value / $total) * 100, 1) : 0;
                                                $color = $chartColor($item['color'] ?? '#0891b2');
                                            @endphp

                                            <div class="rounded-lg border border-slate-100 bg-white p-3 shadow-sm">
                                                <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                                    <span class="flex min-w-0 items-center gap-2 font-bold text-slate-700">
                                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $color }};"></span>
                                                        <span class="truncate">{{ $item['label'] ?? 'Item' }}</span>
                                                    </span>
                                                    <span class="shrink-0 font-black text-slate-950">
                                                        {{ $formatMetric($value, $format) }}
                                                    </span>
                                                </div>

                                                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full transition-all duration-700 ease-out" style="width: {{ $width }}%; background-color: {{ $color }};"></div>
                                                </div>

                                                <div class="mt-2 flex items-center justify-between text-xs font-semibold text-slate-500">
                                                    <span>{{ $share }}% share</span>
                                                    <span>{{ $width }}% of max</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif

            <div class="space-y-6">
                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:shadow-md">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-950">Recent Notices</h3>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Published notices in your scope</p>
                        </div>
                        @if(Route::has('notices.index') && $canSeePermission('notice.view'))
                            <a href="{{ route('notices.index') }}" class="text-xs font-bold text-cyan-700 hover:text-cyan-900">View all</a>
                        @endif
                    </div>

                    @if($notices->isEmpty())
                        <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                            No published notices found.
                        </div>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach($notices as $notice)
                                @php
                                    $noticeDate = $notice->published_at ?? $notice->created_at ?? null;
                                    $noticeDate = $noticeDate ? \Illuminate\Support\Carbon::parse($noticeDate)->format('d M Y') : 'Not dated';
                                @endphp
                                <div class="rounded-lg border border-slate-200 p-3 transition-all duration-200 ease-out hover:border-cyan-200 hover:bg-cyan-50/40">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-sm font-bold text-slate-900">{{ $notice->title }}</p>
                                        <span class="shrink-0 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600">
                                            {{ $notice->priority }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-xs font-semibold text-slate-500">
                                        {{ $notice->category?->name ?? 'General' }} - {{ $noticeDate }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if($recentActivity->isNotEmpty())
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:shadow-md">
                        <h3 class="text-sm font-bold text-slate-950">Recent Activity</h3>
                        <div class="mt-4 space-y-3">
                            @foreach($recentActivity as $activity)
                                <div class="flex items-start gap-3 rounded-lg bg-slate-50 p-3 transition-colors duration-200 ease-out hover:bg-slate-100">
                                    <span class="rounded-md bg-white px-2 py-1 text-[11px] font-black text-slate-600 shadow-sm">
                                        {{ $activity->method }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-800">
                                            {{ $activity->route_name ?? $activity->url }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            {{ $activity->user?->name ?? 'System' }} - {{ optional($activity->created_at)->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </section>
        @endif

    </div>
</x-app-layout>
