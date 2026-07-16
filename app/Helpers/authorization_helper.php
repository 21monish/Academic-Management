<?php

use App\Models\Permission;
use App\Services\AccessScopeService;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

if (! function_exists('hasPermission')) {
    /**
     * Check logged-in user's permission by permission slug.
     *
     * Permissions are assigned directly to users through user_permissions.
     * The user's role is only descriptive/scoping data and is not used for access checks.
     */
    function hasPermission(string $permissionSlug): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        [$module, $action] = normalizePermissionParts($permissionSlug);
        $modules = permissionModuleAliases($module);

        try {
            if (! Schema::hasTable('permissions') || ! Schema::hasTable('user_permissions')) {
                return false;
            }

            $hasPermission = Permission::whereIn('module_name', $modules)
                ->where('action', $action)
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.user_id', $user->user_id);
                })
                ->exists();

            return $hasPermission && app(LicenseService::class)->canAccessPermission($user, $permissionSlug);
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }
}

if (! function_exists('normalizePermissionParts')) {
    function normalizePermissionParts(string $permissionSlug): array
    {
        [$module, $action] = array_pad(explode('.', $permissionSlug, 2), 2, null);

        $module = match ($module) {
            'academic-year' => 'academic_year',
            'elective' => 'elective_group',
            'fee' => 'fees',
            'exam' => 'theory_exam',
            'report' => 'reports',
            default => $module,
        };

        $action = match ($action) {
            'profile' => 'view',
            'edit', 'activate', 'deactivate' => 'update',
            default => $action,
        };

        return [$module, $action];
    }
}

if (! function_exists('permissionModuleAliases')) {
    function permissionModuleAliases(string $module): array
    {
        $aliases = [
            'university' => ['university', 'institution'],
            'college' => ['college', 'institution'],
            'department' => ['department', 'institution'],
            'user' => ['user', 'user_management'],
            'user_permission' => ['user_permission', 'user_management'],
            'role' => ['role', 'user_management'],
            'category' => ['category', 'academic'],
            'programme' => ['programme', 'academic'],
            'semester' => ['semester', 'academic'],
            'subject' => ['subject', 'academic'],
            'curriculum' => ['curriculum', 'academic'],
            'elective_group' => ['elective_group', 'academic'],
            'staff_assignment' => ['staff_assignment', 'timetable', 'attendance'],
            'timetable_slot' => ['timetable_slot', 'timetable', 'attendance'],
            'lecture' => ['lecture', 'attendance'],
            'attendance_summary' => ['attendance_summary', 'attendance'],
            'attendance_defaulter' => ['attendance_defaulter', 'attendance'],
            'exam' => ['exam', 'theory_exam'],
            'exam_subject' => ['exam_subject', 'theory_exam'],
            'grade' => ['grade', 'result'],
            'marks_entry' => ['marks_entry', 'result'],
            'hall_ticket_config' => ['hall_ticket_config', 'hall_ticket'],
            'exam_room' => ['exam_room', 'theory_exam'],
            'seating' => ['seating', 'theory_exam'],
            'invigilator' => ['invigilator', 'theory_exam'],
            'practical_schedule' => ['practical_schedule', 'practical_exam'],
            'practical_batch' => ['practical_batch', 'practical_exam'],
            'practical_mark' => ['practical_mark', 'practical_exam'],
            'fee_category' => ['fee_category', 'fees'],
            'fee_structure' => ['fee_structure', 'fees'],
            'student_ledger' => ['student_ledger', 'fees'],
            'fee_collection' => ['fee_collection', 'fees'],
            'receipt' => ['receipt', 'fees'],
            'concession' => ['concession', 'fees'],
            'scholarship' => ['scholarship', 'fees'],
            'fee_report' => ['fee_report', 'fees'],
            'leave_type' => ['leave_type', 'leave'],
            'leave_balance' => ['leave_balance', 'leave'],
            'leave_application' => ['leave_application', 'leave'],
            'leave_approval' => ['leave_approval', 'leave'],
            'leave_cancellation' => ['leave_cancellation', 'leave'],
            'leave_substitute' => ['leave_substitute', 'leave'],
            'holiday' => ['holiday', 'leave'],
            'notice_category' => ['notice_category', 'notice'],
            'notice_audience' => ['notice_audience', 'notice'],
            'notice_attachment' => ['notice_attachment', 'notice'],
            'notice_acknowledgement' => ['notice_acknowledgement', 'notice'],
            'student_report' => ['student_report', 'reports'],
            'attendance_report' => ['attendance_report', 'reports'],
            'result_card' => ['result_card', 'reports'],
            'fee_receipt_report' => ['fee_receipt_report', 'reports', 'fees'],
            'hall_ticket_report' => ['hall_ticket_report', 'reports', 'hall_ticket'],
            'staff_report' => ['staff_report', 'reports'],
            'activity_log' => ['activity_log', 'reports'],
            'certificate' => ['certificate', 'reports'],
            'license_plan' => ['license_plan'],
            'system_settings' => ['system_settings', 'user_management'],
            'system_health' => ['system_health', 'user_management'],
            'dashboard' => ['dashboard'],
            'profile' => ['profile'],
            'password_change' => ['password_change'],
            'chatbot' => ['chatbot'],
        ];

        return array_values(array_unique($aliases[$module] ?? [$module]));
    }
}

if (! function_exists('currentAccessScope')) {
    function currentAccessScope(): array
    {
        $user = Auth::user();

        if (! $user) {
            return session('access_scope', []);
        }

        try {
            return app(AccessScopeService::class)->forUser($user);
        } catch (\Throwable $exception) {
            report($exception);

            return session('access_scope', []);
        }
    }
}
