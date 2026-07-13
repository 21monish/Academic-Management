@php
    $appBranding = app(\App\Services\SystemSettingService::class)->branding();
    $themeUser = auth()->user();
    $themeRoleName = $themeUser?->role?->role_name ?? 'User';
    $themeIsSuperAdmin = strcasecmp($themeRoleName, 'Super Admin') === 0;
    $themeUser?->loadMissing('university');
    $universityTheme = ! $themeIsSuperAdmin && in_array($themeUser?->university?->theme, ['ocean', 'royal', 'forest'], true)
        ? $themeUser->university->theme
        : 'ocean';
    $footerCredit = $appBranding['footer_text']
        ?: 'Developed by '.$appBranding['created_by'].($appBranding['created_by_contact'] ? ' | '.$appBranding['created_by_contact'] : '');
    $appFaviconUrl = ! $themeIsSuperAdmin && $themeUser?->university?->logo_url
        ? (\Illuminate\Support\Str::startsWith($themeUser->university->logo_url, ['http://', 'https://', '/']) ? $themeUser->university->logo_url : asset($themeUser->university->logo_url))
        : ($appBranding['logo_url'] ?: asset('favicon.svg'));
    $appTitle = ! $themeIsSuperAdmin && $themeUser?->university?->name
        ? $themeUser->university->name
        : $appBranding['application_name'];
    $routeName = request()->route()?->getName();
    $breadcrumbItems = [];

    if ($routeName) {
        $breadcrumbItems[] = ['label' => 'Dashboard', 'url' => \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : null];

        $breadcrumbMap = [
            'universities' => ['section' => 'Institution', 'label' => 'Universities', 'index' => 'universities.index'],
            'colleges' => ['section' => 'Institution', 'label' => 'Colleges', 'index' => 'colleges.index'],
            'departments' => ['section' => 'Institution', 'label' => 'Departments', 'index' => 'departments.index'],
            'users' => ['section' => 'Institution', 'label' => 'Users', 'index' => 'users.index'],
            'roles' => ['section' => 'Institution', 'label' => 'Roles & Permissions', 'index' => 'roles.index'],
            'staff' => ['section' => 'People', 'label' => 'Staff', 'index' => 'staff.index'],
            'students' => ['section' => 'People', 'label' => 'Students', 'index' => 'students.index'],
            'academic.categories' => ['section' => 'People', 'label' => 'People Categories', 'index' => 'academic.categories.index'],
            'academic.academic-years' => ['section' => 'Academic', 'label' => 'Academic Years', 'index' => 'academic.academic-years.index'],
            'academic.programmes' => ['section' => 'Academic', 'label' => 'Programmes', 'index' => 'academic.programmes.index'],
            'academic.semesters' => ['section' => 'Academic', 'label' => 'Semesters', 'index' => 'academic.semesters.index'],
            'academic.subjects' => ['section' => 'Academic', 'label' => 'Subjects', 'index' => 'academic.subjects.index'],
            'academic.curriculum' => ['section' => 'Academic', 'label' => 'Curriculum', 'index' => 'academic.curriculum.index'],
            'academic.elective-groups' => ['section' => 'Academic', 'label' => 'Elective Groups', 'index' => 'academic.elective-groups.index'],
            'attendance.assignments' => ['section' => 'Attendance', 'label' => 'Teaching Staff Subject Assignments', 'index' => 'attendance.assignments'],
            'attendance.slots' => ['section' => 'Attendance', 'label' => 'Timetable Slots', 'index' => 'attendance.slots'],
            'attendance.lectures' => ['section' => 'Attendance', 'label' => 'Lectures', 'index' => 'attendance.lectures'],
            'attendance.mark' => ['section' => 'Attendance', 'label' => 'Lectures', 'index' => 'attendance.lectures', 'action' => 'Mark Attendance'],
            'attendance.summaries' => ['section' => 'Attendance', 'label' => 'Attendance Summary', 'index' => 'attendance.summaries'],
            'attendance.defaulters' => ['section' => 'Attendance', 'label' => 'Defaulters', 'index' => 'attendance.defaulters'],
            'exams.index' => ['section' => 'Exams', 'label' => 'Exams', 'index' => 'exams.index'],
            'exams.subjects' => ['section' => 'Exams', 'label' => 'Exam Subjects', 'index' => 'exams.subjects'],
            'exams.grades' => ['section' => 'Exams', 'label' => 'Grade Master', 'index' => 'exams.grades'],
            'exams.marks' => ['section' => 'Exams', 'label' => 'Marks Entry', 'index' => 'exams.marks'],
            'exams.results' => ['section' => 'Exams', 'label' => 'Results', 'index' => 'exams.results'],
            'exams.backlogs' => ['section' => 'Exams', 'label' => 'Backlogs', 'index' => 'exams.backlogs'],
            'exams.promotions' => ['section' => 'Exams', 'label' => 'Promotions', 'index' => 'exams.promotions'],
            'exams.logistics.configs' => ['section' => 'Exams', 'label' => 'Hall Ticket Config', 'index' => 'exams.logistics.configs'],
            'exams.logistics.tickets' => ['section' => 'Exams', 'label' => 'Hall Tickets', 'index' => 'exams.logistics.tickets'],
            'exams.logistics.rooms' => ['section' => 'Exams', 'label' => 'Exam Rooms', 'index' => 'exams.logistics.rooms'],
            'exams.logistics.seating' => ['section' => 'Exams', 'label' => 'Seating', 'index' => 'exams.logistics.seating'],
            'exams.logistics.invigilators' => ['section' => 'Exams', 'label' => 'Invigilators', 'index' => 'exams.logistics.invigilators'],
            'exams.logistics.practical-schedules' => ['section' => 'Exams', 'label' => 'Practical Schedule', 'index' => 'exams.logistics.practical-schedules'],
            'exams.logistics.practical-batches' => ['section' => 'Exams', 'label' => 'Practical Batches', 'index' => 'exams.logistics.practical-batches'],
            'exams.logistics.practical-marks' => ['section' => 'Exams', 'label' => 'Practical Marks', 'index' => 'exams.logistics.practical-marks'],
            'fees.categories' => ['section' => 'Fees', 'label' => 'Fee Categories', 'index' => 'fees.categories'],
            'fees.structures' => ['section' => 'Fees', 'label' => 'Fee Structures', 'index' => 'fees.structures'],
            'fees.ledgers' => ['section' => 'Fees', 'label' => 'Student Ledgers', 'index' => 'fees.ledgers'],
            'fees.collections' => ['section' => 'Fees', 'label' => 'Fee Collection', 'index' => 'fees.collections'],
            'fees.receipts' => ['section' => 'Fees', 'label' => 'Receipts', 'index' => 'fees.receipts'],
            'fees.concessions' => ['section' => 'Fees', 'label' => 'Concessions', 'index' => 'fees.concessions'],
            'fees.scholarships' => ['section' => 'Fees', 'label' => 'Scholarships', 'index' => 'fees.scholarships'],
            'fees.reports' => ['section' => 'Fees', 'label' => 'Fee Reports', 'index' => 'fees.reports'],
            'leave.types' => ['section' => 'Leave', 'label' => 'Leave Types', 'index' => 'leave.types'],
            'leave.balances' => ['section' => 'Leave', 'label' => 'Leave Balances', 'index' => 'leave.balances'],
            'leave.applications' => ['section' => 'Leave', 'label' => 'Applications', 'index' => 'leave.applications'],
            'leave.approvals' => ['section' => 'Leave', 'label' => 'Approvals', 'index' => 'leave.approvals'],
            'leave.cancellations' => ['section' => 'Leave', 'label' => 'Cancellations', 'index' => 'leave.cancellations'],
            'leave.substitutes' => ['section' => 'Leave', 'label' => 'Substitutes', 'index' => 'leave.substitutes'],
            'leave.holidays' => ['section' => 'Leave', 'label' => 'Holiday Calendar', 'index' => 'leave.holidays'],
            'notices.categories' => ['section' => 'Notices', 'label' => 'Notice Categories', 'index' => 'notices.categories'],
            'notices.index' => ['section' => 'Notices', 'label' => 'Notices', 'index' => 'notices.index'],
            'notices.audiences' => ['section' => 'Notices', 'label' => 'Audience', 'index' => 'notices.audiences'],
            'notices.attachments' => ['section' => 'Notices', 'label' => 'Attachments', 'index' => 'notices.attachments'],
            'notices.acknowledgements' => ['section' => 'Notices', 'label' => 'Acknowledgements', 'index' => 'notices.acknowledgements'],
            'reports.students' => ['section' => 'Reports', 'label' => 'Student Reports', 'index' => 'reports.students'],
            'reports.attendance' => ['section' => 'Reports', 'label' => 'Attendance Reports', 'index' => 'reports.attendance'],
            'reports.results' => ['section' => 'Reports', 'label' => 'Result Cards', 'index' => 'reports.results'],
            'reports.fee-receipts' => ['section' => 'Reports', 'label' => 'Fee Receipts', 'index' => 'reports.fee-receipts'],
            'reports.hall-tickets' => ['section' => 'Reports', 'label' => 'Hall Ticket PDF', 'index' => 'reports.hall-tickets'],
            'reports.staff' => ['section' => 'Reports', 'label' => 'Staff Reports', 'index' => 'reports.staff'],
            'reports.activity' => ['section' => 'Reports', 'label' => 'Activity Logs', 'index' => 'reports.activity'],
            'system.settings' => ['section' => 'System', 'label' => 'System Settings', 'index' => 'system.settings'],
            'system.health' => ['section' => 'System', 'label' => 'System Health', 'index' => 'system.health'],
            'profile.edit' => ['section' => 'Account', 'label' => 'Profile', 'index' => 'profile.edit'],
            'password.change.show' => ['section' => 'Account', 'label' => 'Change Password', 'index' => 'password.change.show'],
        ];

        $matchedKey = collect(array_keys($breadcrumbMap))
            ->sortByDesc(fn ($key) => strlen($key))
            ->first(fn ($key) => $routeName === $key || str_starts_with($routeName, $key.'.'));

        if ($routeName !== 'dashboard' && $matchedKey) {
            $config = $breadcrumbMap[$matchedKey];
            $breadcrumbItems[] = ['label' => $config['section']];

            $isIndexRoute = $routeName === $config['index'];
            $breadcrumbItems[] = [
                'label' => $config['label'],
                'url' => ! $isIndexRoute && \Illuminate\Support\Facades\Route::has($config['index']) ? route($config['index']) : null,
            ];

            $action = str($routeName)->after($matchedKey.'.')->toString();
            $actionLabels = [
                'create' => 'Create',
                'edit' => 'Edit',
                'show' => 'View',
                'permissions' => 'Permissions',
                'permissions.edit' => 'Permissions',
                'print' => 'Print',
            ];

            if (! $isIndexRoute && isset($config['action'])) {
                $breadcrumbItems[] = ['label' => $config['action']];
            } elseif (! $isIndexRoute && isset($actionLabels[$action])) {
                $breadcrumbItems[] = ['label' => $actionLabels[$action]];
            }
        } elseif ($routeName !== 'dashboard') {
            $breadcrumbItems[] = ['label' => str($routeName)->replace(['.', '-'], ' ')->title()->toString()];
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $appTitle }}</title>
        <link rel="icon" href="{{ $appFaviconUrl }}">
        <link rel="shortcut icon" href="{{ $appFaviconUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @include('layouts.partials.vite')
    </head>
    <body class="app-shell theme-{{ $universityTheme }} font-sans antialiased text-slate-900">
        <div class="min-h-screen bg-slate-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-slate-200 bg-white/90 shadow-sm backdrop-blur lg:pl-72">
                    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="page-enter lg:pl-72">
                @auth
                    @if(count($breadcrumbItems))
                        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                            @include('partials._breadcrumb', ['items' => $breadcrumbItems])
                        </div>
                    @endif
                @endauth

                {{ $slot }}
            </main>

            <footer class="border-t border-slate-200 bg-white/90 py-4 text-center text-xs font-semibold text-slate-500 lg:pl-72">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {{ $footerCredit }}
                </div>
            </footer>

            @auth
                @if(hasPermission('chatbot.ask'))
                @include('layouts.chatbot')
                @endif
            @endauth
        </div>
        @include('layouts.partials.table-srno')
        @include('layouts.partials.table-tools')
    </body>
</html>
