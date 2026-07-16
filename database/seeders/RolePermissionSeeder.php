<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    private const SYSTEM_ROLES = [
        'Super Admin' => 'Full system access',
        'Admin' => 'Institution administration',
        'Student' => 'Student self-service access',
    ];

    private const LEGACY_SYSTEM_ROLES = [
        'Principal',
        'HOD',
        'Teaching Staff',
        'Non-Teaching Staff',
        'Accountant',
        'University Admin',
    ];

    public function run(): void
    {
        foreach (self::SYSTEM_ROLES as $roleName => $description) {
            UserRole::updateOrCreate([
                'role_name' => $roleName,
                'university_id' => null,
            ], [
                'description' => $description,
                'is_system_role' => true,
                'is_active' => true,
            ]);
        }

        $this->retireLegacySystemRoles();
        $this->seedPermissions();
        $this->syncDefaultRolePermissions();
    }

    private function retireLegacySystemRoles(): void
    {
        UserRole::query()
            ->whereNull('university_id')
            ->whereIn('role_name', self::LEGACY_SYSTEM_ROLES)
            ->update([
                'is_system_role' => false,
                'is_active' => false,
            ]);
    }

    private function seedPermissions(): void
    {
        $modules = [
            'dashboard', 'profile', 'password_change', 'chatbot',
            'university', 'college', 'department', 'user', 'user_permission', 'role',
            'staff', 'student', 'category',
            'academic_year', 'programme', 'semester', 'subject', 'curriculum', 'elective_group',
            'staff_assignment', 'timetable_slot', 'lecture', 'attendance_summary', 'attendance_defaulter',
            'exam', 'exam_subject', 'grade', 'marks_entry', 'result', 'backlog', 'promotion',
            'hall_ticket_config', 'hall_ticket', 'exam_room', 'seating', 'invigilator',
            'practical_schedule', 'practical_batch', 'practical_mark',
            'fee_category', 'fee_structure', 'student_ledger', 'fee_collection', 'receipt',
            'concession', 'scholarship', 'fee_report',
            'leave_type', 'leave_balance', 'leave_application', 'leave_approval',
            'leave_cancellation', 'leave_substitute', 'holiday',
            'notice_category', 'notice', 'notice_audience', 'notice_attachment', 'notice_acknowledgement',
            'student_report', 'attendance_report', 'result_card', 'fee_receipt_report',
            'hall_ticket_report', 'staff_report', 'activity_log', 'certificate',
            'system_settings', 'system_health',
            // Legacy broad modules kept for old routes/users during transition.
            'institution', 'academic', 'timetable', 'attendance', 'user_management',
            'theory_exam', 'practical_exam', 'fees', 'leave', 'reports',
        ];

        $actions = ['view', 'create', 'update', 'delete', 'approve'];
        $pageActions = [
            'dashboard' => ['view'],
            'profile' => ['view', 'update', 'delete'],
            'password_change' => ['view', 'update'],
            'chatbot' => ['ask', 'teach'],
            'user_permission' => ['view', 'update'],
            'certificate' => ['view'],
        ];

        foreach ($modules as $module) {
            foreach ($pageActions[$module] ?? $actions as $action) {
                Permission::updateOrCreate(
                    ['module_name' => $module, 'action' => $action],
                    ['description' => ucfirst($action).' '.str_replace('_', ' ', $module)]
                );
            }
        }
    }

    private function syncDefaultRolePermissions(): void
    {
        $permissions = Permission::query()
            ->get()
            ->groupBy('module_name')
            ->map(fn ($items) => $items->keyBy('action'));

        $matrix = [
            'Super Admin' => ['*' => ['*']],
            'Admin' => [
                'dashboard' => ['view'],
                'profile' => ['view', 'update'],
                'password_change' => ['view', 'update'],
                'chatbot' => ['ask'],
                'university' => ['view', 'create', 'update', 'delete'],
                'college' => ['view', 'create', 'update', 'delete', 'approve'],
                'department' => ['view', 'create', 'update', 'delete', 'approve'],
                'user' => ['view', 'create', 'update', 'delete'],
                'user_permission' => ['view', 'update'],
                'role' => ['view', 'create', 'update', 'delete'],
                'institution' => ['view', 'create', 'update', 'approve'],
                'category' => ['view', 'create', 'update', 'delete', 'approve'],
                'academic_year' => ['view', 'create', 'update', 'delete', 'approve'],
                'programme' => ['view', 'create', 'update', 'delete', 'approve'],
                'semester' => ['view', 'create', 'update', 'delete', 'approve'],
                'subject' => ['view', 'create', 'update', 'delete', 'approve'],
                'curriculum' => ['view', 'create', 'update', 'delete', 'approve'],
                'elective_group' => ['view', 'create', 'update', 'delete', 'approve'],
                'academic' => ['view', 'create', 'update', 'approve'],
                'staff' => ['view', 'create', 'update', 'delete', 'approve'],
                'student' => ['view', 'create', 'update', 'delete', 'approve'],
                'staff_assignment' => ['view', 'create', 'update', 'delete', 'approve'],
                'timetable_slot' => ['view', 'create', 'update', 'delete', 'approve'],
                'lecture' => ['view', 'create', 'update', 'delete', 'approve'],
                'attendance_summary' => ['view'],
                'attendance_defaulter' => ['view'],
                'timetable' => ['view', 'create', 'update', 'approve'],
                'attendance' => ['view', 'approve'],
                'exam' => ['view', 'create', 'update', 'delete', 'approve'],
                'exam_subject' => ['view', 'create', 'update', 'delete', 'approve'],
                'grade' => ['view', 'create', 'update', 'delete'],
                'marks_entry' => ['view', 'create', 'update', 'delete'],
                'theory_exam' => ['view', 'create', 'update', 'approve'],
                'practical_schedule' => ['view', 'create', 'update', 'delete', 'approve'],
                'practical_batch' => ['view', 'create', 'update', 'delete', 'approve'],
                'practical_mark' => ['view', 'create', 'update', 'delete', 'approve'],
                'practical_exam' => ['view', 'create', 'update', 'approve'],
                'result' => ['view', 'create', 'update', 'delete', 'approve'],
                'backlog' => ['view', 'create', 'update', 'delete', 'approve'],
                'promotion' => ['view', 'create', 'update', 'delete', 'approve'],
                'hall_ticket_config' => ['view', 'create', 'update', 'delete', 'approve'],
                'hall_ticket' => ['view', 'create', 'update', 'delete', 'approve'],
                'exam_room' => ['view', 'create', 'update', 'delete'],
                'seating' => ['view', 'create', 'update', 'delete'],
                'invigilator' => ['view', 'create', 'update', 'delete'],
                'fee_category' => ['view', 'create', 'update', 'delete'],
                'fee_structure' => ['view', 'create', 'update', 'delete'],
                'student_ledger' => ['view', 'create', 'update', 'delete'],
                'fee_collection' => ['view', 'create', 'update', 'delete'],
                'receipt' => ['view'],
                'concession' => ['view', 'create', 'update', 'delete', 'approve'],
                'scholarship' => ['view', 'create', 'update', 'delete'],
                'fee_report' => ['view'],
                'fees' => ['view', 'approve'],
                'leave_type' => ['view', 'create', 'update', 'delete'],
                'leave_balance' => ['view', 'create', 'update', 'delete'],
                'leave_application' => ['view', 'create', 'update', 'delete'],
                'leave_approval' => ['view', 'update', 'delete', 'approve'],
                'leave_cancellation' => ['view', 'create', 'update', 'delete'],
                'leave_substitute' => ['view', 'create', 'update', 'delete'],
                'holiday' => ['view', 'create', 'update', 'delete'],
                'leave' => ['view', 'approve'],
                'notice_category' => ['view', 'create', 'update', 'delete', 'approve'],
                'notice' => ['view', 'create', 'update', 'delete', 'approve'],
                'notice_audience' => ['view', 'create', 'update', 'delete', 'approve'],
                'notice_attachment' => ['view', 'create', 'update', 'delete'],
                'notice_acknowledgement' => ['view', 'create', 'update', 'delete', 'approve'],
                'student_report' => ['view'],
                'attendance_report' => ['view'],
                'result_card' => ['view'],
                'fee_receipt_report' => ['view'],
                'hall_ticket_report' => ['view'],
                'staff_report' => ['view'],
                'activity_log' => ['view'],
                'certificate' => ['view'],
                'system_settings' => ['view', 'update'],
                'system_health' => ['view'],
                'user_management' => ['view', 'create', 'update'],
                'reports' => ['view'],
            ],
            'Student' => [
                'dashboard' => ['view'],
                'profile' => ['view', 'update'],
                'password_change' => ['view', 'update'],
                'chatbot' => ['ask'],
                'attendance_summary' => ['view'],
                'result' => ['view'],
                'hall_ticket' => ['view'],
                'receipt' => ['view'],
                'leave_application' => ['view', 'create', 'update', 'delete'],
                'notice' => ['view'],
                'notice_acknowledgement' => ['view', 'create'],
                'attendance_report' => ['view'],
                'result_card' => ['view'],
                'fee_receipt_report' => ['view'],
                'hall_ticket_report' => ['view'],
                'reports' => ['view'],
            ],
        ];

        foreach ($matrix as $roleName => $moduleActions) {
            $role = UserRole::where('role_name', $roleName)
                ->whereNull('university_id')
                ->first();

            if (! $role) {
                continue;
            }

            if (isset($moduleActions['*'])) {
                $role->permissions()->sync(Permission::pluck('permission_id')->all());
                continue;
            }

            $permissionIds = [];

            foreach ($moduleActions as $module => $actions) {
                foreach ($actions as $action) {
                    $permission = $permissions[$module][$action] ?? null;

                    if ($permission) {
                        $permissionIds[] = $permission->permission_id;
                    }
                }
            }

            $role->permissions()->sync(array_unique($permissionIds));
        }
    }
}
