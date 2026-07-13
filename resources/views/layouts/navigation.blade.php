@php
    $user = Auth::user();
    $mustChangePassword = $user?->must_change_password;
    $roleName = $user?->role?->role_name ?? 'User';
    $isSuperAdmin = strcasecmp($roleName, 'Super Admin') === 0;
    $user?->loadMissing('university');
    $systemBranding = app(\App\Services\SystemSettingService::class)->branding();
    $brandUniversity = $isSuperAdmin ? null : $user?->university;
    $brandName = $brandUniversity?->name ?? $systemBranding['application_name'];
    $brandSubtitle = $systemBranding['application_short_name'];
    $brandLogoUrl = $brandUniversity?->logo_url
        ? (\Illuminate\Support\Str::startsWith($brandUniversity->logo_url, ['http://', 'https://', '/']) ? $brandUniversity->logo_url : asset($brandUniversity->logo_url))
        : $systemBranding['logo_url'];
    $brandInitials = collect(explode(' ', $brandName))
        ->filter()
        ->take(2)
        ->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
        ->join('') ?: 'GI';
    $navigationRoleName = strcasecmp($roleName, 'admin') === 0 ? 'University Admin' : $roleName;
    $displayRoleName = $navigationRoleName === 'University Admin'
        ? 'University Admin'
        : $roleName;
    $dashboardLabel = 'Dashboard';

    $navSections = $mustChangePassword
        ? [
            'Account' => hasPermission('password_change.view') ? [
                ['label' => 'Change Password', 'route' => 'password.change.show', 'active' => 'password.change*', 'icon' => 'key', 'permission' => 'password_change.view'],
            ] : [],
        ]
        : [
            'Home' => [
                ['label' => $dashboardLabel, 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
            ],
        ];

    $canAccessNavItem = function (array $item) use ($navigationRoleName): bool {
        if (! Route::has($item['route'])) {
            return false;
        }

        if (! empty($item['onlyRoles']) && ! in_array($navigationRoleName, (array) $item['onlyRoles'], true)) {
            return false;
        }

        if (! empty($item['exceptRoles']) && in_array($navigationRoleName, (array) $item['exceptRoles'], true)) {
            return false;
        }

        if (empty($item['permission'])) {
            return true;
        }

        foreach ((array) $item['permission'] as $permission) {
            if (hasPermission($permission)) {
                return true;
            }
        }

        return false;
    };

    if (! $mustChangePassword) {
        $moduleSections = [
            'Institution' => [
                ['label' => 'Universities', 'route' => 'universities.index', 'active' => 'universities.*', 'icon' => 'building', 'permission' => 'university.view'],
                ['label' => 'Colleges', 'route' => 'colleges.index', 'active' => 'colleges.*', 'icon' => 'campus', 'permission' => 'college.view'],
                ['label' => 'Departments', 'route' => 'departments.index', 'active' => 'departments.*', 'icon' => 'layers', 'permission' => 'department.view'],
                ['label' => 'Users', 'route' => 'users.index', 'active' => 'users.*', 'icon' => 'users', 'permission' => ['user.view', 'user_permission.view', 'user_permission.update']],
                ['label' => 'Roles & Permissions', 'route' => 'roles.index', 'active' => 'roles.*', 'icon' => 'shield', 'permission' => 'role.view'],
            ],
            'People' => [
                ['label' => 'Staff', 'route' => 'staff.index', 'active' => 'staff.*', 'icon' => 'briefcase', 'permission' => 'staff.view'],
                ['label' => 'Subject Assignments', 'route' => 'attendance.assignments', 'active' => 'attendance.assignments', 'icon' => 'subject', 'permission' => 'staff_assignment.view'],
                ['label' => 'Students', 'route' => 'students.index', 'active' => 'students.*', 'icon' => 'graduation', 'permission' => 'student.view'],
                ['label' => 'People Categories', 'route' => 'academic.categories.index', 'active' => 'academic.categories.*', 'icon' => 'layers', 'permission' => 'category.view'],
            ],
            'Academic' => [
                ['label' => 'Academic Years', 'route' => 'academic.academic-years.index', 'active' => 'academic.academic-years.*', 'icon' => 'calendar', 'permission' => 'academic_year.view'],
                ['label' => 'Programmes', 'route' => 'academic.programmes.index', 'active' => 'academic.programmes.*', 'icon' => 'book', 'permission' => 'programme.view'],
                ['label' => 'Semesters', 'route' => 'academic.semesters.index', 'active' => 'academic.semesters.*', 'icon' => 'calendar', 'permission' => 'semester.view'],
                ['label' => 'Subjects', 'route' => 'academic.subjects.index', 'active' => 'academic.subjects.*', 'icon' => 'subject', 'permission' => 'subject.view'],
                ['label' => 'Curriculum', 'route' => 'academic.curriculum.index', 'active' => 'academic.curriculum.*', 'icon' => 'book', 'permission' => 'curriculum.view'],
                ['label' => 'Elective Groups', 'route' => 'academic.elective-groups.index', 'active' => 'academic.elective-groups.*', 'icon' => 'layers', 'permission' => 'elective_group.view'],
            ],
            'Attendance' => [
                ['label' => 'Teaching Staff Subject Assignments', 'route' => 'attendance.assignments', 'active' => 'attendance.assignments', 'icon' => 'subject', 'permission' => 'staff_assignment.view'],
                ['label' => 'Timetable Slots', 'route' => 'attendance.slots', 'active' => 'attendance.slots', 'icon' => 'calendar', 'permission' => 'timetable_slot.view'],
                ['label' => 'Lectures', 'route' => 'attendance.lectures', 'active' => 'attendance.lectures', 'icon' => 'subject', 'permission' => 'lecture.view'],
                ['label' => 'Attendance Summary', 'route' => 'attendance.summaries', 'active' => 'attendance.summaries', 'icon' => 'dashboard', 'permission' => 'attendance_summary.view'],
                ['label' => 'Defaulters', 'route' => 'attendance.defaulters', 'active' => 'attendance.defaulters', 'icon' => 'users', 'permission' => 'attendance_defaulter.view'],
            ],
            'Exams' => [
                ['label' => 'Exams', 'route' => 'exams.index', 'active' => 'exams.index', 'icon' => 'book', 'permission' => 'exam.view'],
                ['label' => 'Exam Subjects', 'route' => 'exams.subjects', 'active' => 'exams.subjects', 'icon' => 'subject', 'permission' => 'exam_subject.view'],
                ['label' => 'Grade Master', 'route' => 'exams.grades', 'active' => 'exams.grades', 'icon' => 'layers', 'permission' => 'grade.view'],
                ['label' => 'Marks Entry', 'route' => 'exams.marks', 'active' => 'exams.marks', 'icon' => 'subject', 'permission' => ['marks_entry.create', 'marks_entry.update']],
                ['label' => 'Results', 'route' => 'exams.results', 'active' => 'exams.results', 'icon' => 'dashboard', 'permission' => 'result.view'],
                ['label' => 'Backlogs', 'route' => 'exams.backlogs', 'active' => 'exams.backlogs', 'icon' => 'layers', 'permission' => 'backlog.view'],
                ['label' => 'Promotions', 'route' => 'exams.promotions', 'active' => 'exams.promotions', 'icon' => 'graduation', 'permission' => 'promotion.view'],
                ['label' => 'Hall Ticket Config', 'route' => 'exams.logistics.configs', 'active' => 'exams.logistics.configs', 'icon' => 'settings', 'permission' => 'hall_ticket_config.view'],
                ['label' => 'Hall Tickets', 'route' => 'exams.logistics.tickets', 'active' => 'exams.logistics.tickets', 'icon' => 'ticket', 'permission' => 'hall_ticket.view'],
                ['label' => 'Exam Rooms', 'route' => 'exams.logistics.rooms', 'active' => 'exams.logistics.rooms', 'icon' => 'door', 'permission' => 'exam_room.view'],
                ['label' => 'Seating', 'route' => 'exams.logistics.seating', 'active' => 'exams.logistics.seating', 'icon' => 'seat', 'permission' => 'seating.view'],
                ['label' => 'Invigilators', 'route' => 'exams.logistics.invigilators', 'active' => 'exams.logistics.invigilators', 'icon' => 'shield', 'permission' => 'invigilator.view'],
                ['label' => 'Practical Schedule', 'route' => 'exams.logistics.practical-schedules', 'active' => 'exams.logistics.practical-schedules', 'icon' => 'calendar', 'permission' => 'practical_schedule.view'],
                ['label' => 'Practical Batches', 'route' => 'exams.logistics.practical-batches', 'active' => 'exams.logistics.practical-batches', 'icon' => 'users', 'permission' => 'practical_batch.view'],
                ['label' => 'Practical Marks', 'route' => 'exams.logistics.practical-marks', 'active' => 'exams.logistics.practical-marks', 'icon' => 'check', 'permission' => 'practical_mark.view'],
            ],
            'Fees' => [
                ['label' => 'Fee Categories', 'route' => 'fees.categories', 'active' => 'fees.categories', 'icon' => 'tag', 'permission' => 'fee_category.view'],
                ['label' => 'Fee Structures', 'route' => 'fees.structures', 'active' => 'fees.structures', 'icon' => 'layers', 'permission' => 'fee_structure.view'],
                ['label' => 'Student Ledgers', 'route' => 'fees.ledgers', 'active' => 'fees.ledgers', 'icon' => 'ledger', 'permission' => 'student_ledger.view'],
                ['label' => 'Fee Collection', 'route' => 'fees.collections', 'active' => 'fees.collections', 'icon' => 'money', 'permission' => ['fee_collection.view', 'fee_collection.create', 'fee_collection.update']],
                ['label' => 'Receipts', 'route' => 'fees.receipts', 'active' => 'fees.receipts', 'icon' => 'receipt', 'permission' => 'receipt.view'],
                ['label' => 'Concessions', 'route' => 'fees.concessions', 'active' => 'fees.concessions', 'icon' => 'discount', 'permission' => 'concession.view'],
                ['label' => 'Scholarships', 'route' => 'fees.scholarships', 'active' => 'fees.scholarships', 'icon' => 'graduation', 'permission' => 'scholarship.view'],
                ['label' => 'Fee Reports', 'route' => 'fees.reports', 'active' => 'fees.reports', 'icon' => 'chart', 'permission' => 'fee_report.view'],
            ],
            'Leave' => [
                ['label' => 'Leave Types', 'route' => 'leave.types', 'active' => 'leave.types', 'icon' => 'tag', 'permission' => 'leave_type.view'],
                ['label' => 'Leave Balances', 'route' => 'leave.balances', 'active' => 'leave.balances', 'icon' => 'ledger', 'permission' => 'leave_balance.view'],
                ['label' => 'Applications', 'route' => 'leave.applications', 'active' => 'leave.applications', 'icon' => 'calendar', 'permission' => 'leave_application.view'],
                ['label' => 'Approvals', 'route' => 'leave.approvals', 'active' => 'leave.approvals', 'icon' => 'check', 'permission' => ['leave_approval.approve', 'leave_approval.update']],
                ['label' => 'Cancellations', 'route' => 'leave.cancellations', 'active' => 'leave.cancellations', 'icon' => 'x', 'permission' => 'leave_cancellation.view'],
                ['label' => 'Substitutes', 'route' => 'leave.substitutes', 'active' => 'leave.substitutes', 'icon' => 'users', 'permission' => 'leave_substitute.view'],
                ['label' => 'Holiday Calendar', 'route' => 'leave.holidays', 'active' => 'leave.holidays', 'icon' => 'calendar', 'permission' => 'holiday.view'],
            ],
            'Notices' => [
                ['label' => 'Notice Categories', 'route' => 'notices.categories', 'active' => 'notices.categories', 'icon' => 'tag', 'permission' => 'notice_category.view'],
                ['label' => 'Notices', 'route' => 'notices.index', 'active' => 'notices.index', 'icon' => 'notice', 'permission' => 'notice.view'],
                ['label' => 'Audience', 'route' => 'notices.audiences', 'active' => 'notices.audiences', 'icon' => 'users', 'permission' => ['notice_audience.view', 'notice_audience.create', 'notice_audience.update', 'notice_audience.approve']],
                ['label' => 'Attachments', 'route' => 'notices.attachments', 'active' => 'notices.attachments', 'icon' => 'attachment', 'permission' => 'notice_attachment.view'],
                ['label' => 'Acknowledgements', 'route' => 'notices.acknowledgements', 'active' => 'notices.acknowledgements', 'icon' => 'check', 'permission' => ['notice_acknowledgement.view', 'notice_acknowledgement.create', 'notice_acknowledgement.update', 'notice_acknowledgement.approve']],
            ],
            'Reports' => [
                ['label' => 'Student Reports', 'route' => 'reports.students', 'active' => 'reports.students', 'icon' => 'graduation', 'permission' => 'student_report.view'],
                ['label' => 'Attendance Reports', 'route' => 'reports.attendance', 'active' => 'reports.attendance', 'icon' => 'chart', 'permission' => 'attendance_report.view'],
                ['label' => 'Result Cards', 'route' => 'reports.results', 'active' => 'reports.results', 'icon' => 'check', 'permission' => 'result_card.view'],
                ['label' => 'Fee Receipts', 'route' => 'reports.fee-receipts', 'active' => 'reports.fee-receipts', 'icon' => 'receipt', 'permission' => 'fee_receipt_report.view'],
                ['label' => 'Hall Ticket PDF', 'route' => 'reports.hall-tickets', 'active' => 'reports.hall-tickets', 'icon' => 'ticket', 'permission' => 'hall_ticket_report.view'],
                ['label' => 'Staff Reports', 'route' => 'reports.staff', 'active' => 'reports.staff', 'icon' => 'briefcase', 'permission' => 'staff_report.view'],
                ['label' => 'Activity Logs', 'route' => 'reports.activity', 'active' => 'reports.activity', 'icon' => 'chart', 'permission' => 'activity_log.view'],
            ],
            'System' => [
                ['label' => 'System Settings', 'route' => 'system.settings', 'active' => 'system.settings*', 'icon' => 'settings', 'permission' => 'system_settings.view'],
                ['label' => 'System Health', 'route' => 'system.health', 'active' => 'system.health', 'icon' => 'shield', 'permission' => 'system_health.view'],
            ],
        ];

        foreach ($moduleSections as $section => $items) {
            $visibleItems = array_values(array_filter($items, $canAccessNavItem));

            if ($visibleItems) {
                $navSections[$section] = $visibleItems;
            }
        }
    }

    $navIcon = function (string $icon): string {
        return match ($icon) {
            'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-4H4v4Z"/>',
            'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 8h1m4 0h1M9 12h1m4 0h1M9 16h1m4 0h1"/>',
            'campus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 10 9-6 9 6M5 10v9m4-9v9m6-9v9m4-9v9M3 19h18"/>',
            'layers' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 3 9 5-9 5-9-5 9-5Zm-7 9 7 4 7-4M5 16l7 4 7-4"/>',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 0 1-8 0m8 0c2.2.5 4 2 4 4v2H4v-2c0-2 1.8-3.5 4-4"/>',
            'briefcase' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m-9 0h10a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Zm6 5v1"/>',
            'graduation' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 4 9 5-9 5-9-5 9-5Zm-5 8v4c0 1.7 2.2 3 5 3s5-1.3 5-3v-4"/>',
            'book' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a3 3 0 0 1 3-3h11v17H8a3 3 0 0 0-3 3V5Zm0 0v17"/>',
            'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/>',
            'subject' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h12v16H6V4Zm3 4h6M9 12h6M9 16h4"/>',
            'settings' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm8 4h2m-20 0h2m14.1 6.1 1.4 1.4M4.5 4.5l1.4 1.4m12.2-1.4-1.4 1.4M4.5 19.5l1.4-1.4"/>',
            'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4V7Zm6 3h4m-4 4h4"/>',
            'door' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 21V4a1 1 0 0 1 1-1h10v18M6 21h12M14 12h.01"/>',
            'seat' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a4 4 0 0 1 8 0v4M5 11h14v5a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-5Zm3 7v3m8-3v3"/>',
            'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3 5 6v5c0 4.5 2.8 8.3 7 10 4.2-1.7 7-5.5 7-10V6l-7-3Zm-3 9 2 2 4-5"/>',
            'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12.5 9 17l11-12M5 21h14"/>',
            'tag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 11V5h6l10 10-6 6L4 11Zm4-3h.01"/>',
            'ledger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 3h12v18H6V3Zm3 5h6M9 12h6M9 16h3"/>',
            'money' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16v10H4V7Zm8 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm-6 1h2m8 4h2"/>',
            'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h10v18l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5V3Zm3 5h4m-4 4h4m-4 4h2"/>',
            'discount' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 19 14-14M7.5 8.5h.01M16.5 15.5h.01M9 8.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm9 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h16M7 16V9m5 7V5m5 11v-4"/>',
            'x' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/>',
            'notice' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h12v16H6V4Zm3 4h6M9 12h6M9 16h3"/>',
            'attachment' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12.5 14.5 6a3 3 0 0 1 4.2 4.2l-8 8a5 5 0 0 1-7.1-7.1l8.5-8.5"/>',
            default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>',
        };
    };

@endphp

<nav>
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-slate-200 bg-white shadow-sm lg:flex lg:flex-col">
        <div class="border-b border-slate-200 px-5 py-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                @if($brandLogoUrl)
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-10 w-10 rounded-lg border border-slate-200 bg-white object-contain p-1 shadow-sm">
                @else
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-cyan-700 text-sm font-bold text-white shadow-sm">{{ $brandInitials }}</span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-base font-bold text-slate-950">{{ $brandName }}</span>
                    <span class="block truncate text-xs font-medium text-slate-500">{{ $brandSubtitle }}</span>
                </span>
            </a>
            <div class="mt-5 rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-2">
                <p class="text-xs font-semibold text-cyan-800">{{ $displayRoleName }}</p>
                <p class="mt-0.5 truncate text-xs text-cyan-700">{{ $user?->email }}</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4">
            @foreach($navSections as $section => $items)
                @continue(empty($items))
                @php
                    $sectionActive = collect($items)->contains(fn ($item) => request()->routeIs($item['active']));
                    $sectionIcon = $items[0]['icon'] ?? 'dashboard';
                    $sectionPinnedOpen = $sectionActive || $section === 'Account';
                @endphp
                @if($section === 'Home')
                    @foreach($items as $item)
                        @php($active = request()->routeIs($item['active']))
                        <a href="{{ route($item['route']) }}"
                           class="{{ $active ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} group mb-2 flex h-10 items-center gap-3 rounded-lg px-3 text-sm font-semibold transition">
                            <span class="{{ $active ? 'text-cyan-200' : 'text-slate-400 group-hover:text-cyan-700' }} grid h-5 w-5 place-items-center">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $navIcon($item['icon']) !!}</svg>
                            </span>
                            <span class="truncate">{{ __($item['label']) }}</span>
                        </a>
                    @endforeach
                    @continue
                @endif
                <details class="nav-dropdown mb-2 group" data-pinned-open="{{ $sectionPinnedOpen ? 'true' : 'false' }}" onmouseenter="this.open = true" onmouseleave="if (this.dataset.pinnedOpen !== 'true') this.open = false" @if($sectionPinnedOpen) open @endif>
                    <summary class="{{ $sectionActive ? 'bg-cyan-50 text-cyan-900 ring-1 ring-cyan-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex cursor-pointer list-none items-center gap-3 rounded-lg px-3 py-2 text-left text-xs font-black uppercase transition [&::-webkit-details-marker]:hidden">
                        <span class="{{ $sectionActive ? 'text-cyan-700' : 'text-slate-400' }} grid h-5 w-5 place-items-center">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $navIcon($sectionIcon) !!}</svg>
                        </span>
                        <span class="min-w-0 flex-1 truncate">{{ $section }}</span>
                        <svg class="h-4 w-4 shrink-0 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>
                    <div class="nav-dropdown-panel mt-1 space-y-1 ps-3">
                        @foreach($items as $item)
                            @php($active = request()->routeIs($item['active']))
                            <a href="{{ route($item['route']) }}"
                               class="{{ $active ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} group flex h-10 items-center gap-3 rounded-lg px-3 text-sm font-semibold transition">
                                <span class="{{ $active ? 'text-cyan-200' : 'text-slate-400 group-hover:text-cyan-700' }} grid h-5 w-5 place-items-center">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $navIcon($item['icon']) !!}</svg>
                                </span>
                                <span class="truncate">{{ __($item['label']) }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>

        <div class="border-t border-slate-200 p-3">
            <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3">
                <div class="grid h-9 w-9 place-items-center rounded-lg bg-white text-sm font-bold text-cyan-700 shadow-sm">
                    {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ $user?->name }}</p>
                    @if(hasPermission('profile.view'))
                        <a href="{{ route('profile.edit') }}" class="text-xs font-semibold text-cyan-700 hover:text-cyan-800">Profile</a>
                    @endif
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg px-2 py-1 text-xs font-bold text-slate-500 transition hover:bg-white hover:text-red-600">Exit</button>
                </form>
            </div>
        </div>
    </aside>

    <details class="group sticky top-0 z-30 border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur lg:hidden">
        <summary class="flex cursor-pointer list-none items-center justify-between [&::-webkit-details-marker]:hidden">
            <span class="flex items-center gap-3">
                @if($brandLogoUrl)
                    <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }} logo" class="h-10 w-10 rounded-lg border border-slate-200 bg-white object-contain p-1">
                @else
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-cyan-700 text-xs font-bold text-white">{{ $brandInitials }}</span>
                @endif
                <span>
                    <span class="block text-sm font-bold text-slate-950">{{ $brandName }}</span>
                    <span class="block text-xs text-slate-500">{{ $brandSubtitle }}</span>
                </span>
            </span>

            <span class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-slate-700 transition hover:bg-slate-100">
                <svg class="h-5 w-5 group-open:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </span>
        </summary>

        <div class="mt-4 max-h-[75vh] overflow-y-auto border-t border-slate-100 pt-4">
            @foreach($navSections as $section => $items)
                @continue(empty($items))
                @php($sectionActive = collect($items)->contains(fn ($item) => request()->routeIs($item['active'])))
                @if($section === 'Home')
                    @foreach($items as $item)
                        <a href="{{ route($item['route']) }}"
                           class="{{ request()->routeIs($item['active']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }} mb-2 block rounded-lg px-3 py-2 text-sm font-semibold">
                            {{ __($item['label']) }}
                        </a>
                    @endforeach
                    @continue
                @endif
                <details class="nav-dropdown mb-2 group" @if($sectionActive || in_array($section, ['Home', 'Account'], true)) open @endif>
                    <summary class="{{ $sectionActive ? 'bg-cyan-50 text-cyan-900' : 'text-slate-600 hover:bg-slate-50' }} flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-black uppercase [&::-webkit-details-marker]:hidden">
                        <span>{{ $section }}</span>
                        <svg class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>
                    <div class="nav-dropdown-panel mt-1 space-y-1 ps-2">
                        @foreach($items as $item)
                            <a href="{{ route($item['route']) }}"
                               class="{{ request()->routeIs($item['active']) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }} block rounded-lg px-3 py-2 text-sm font-semibold">
                                {{ __($item['label']) }}
                            </a>
                        @endforeach
                    </div>
                </details>
            @endforeach

            <div class="border-t border-slate-100 pt-4">
                <div class="px-3 pb-3">
                    <p class="text-xs font-semibold text-cyan-700">{{ $displayRoleName }}</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $user?->name }}</p>
                    <p class="text-xs text-slate-500">{{ $user?->email }}</p>
                </div>
                @if(hasPermission('profile.view'))
                    <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Profile</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-red-600 hover:bg-red-50">Log Out</button>
                </form>
            </div>
        </div>
    </details>
</nav>
