<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LicenseService
{
    private const ACTIVE_STATUSES = ['Active', 'Trial'];

    private const MODULE_FEATURES = [
        'dashboard' => 'core',
        'profile' => 'core',
        'password_change' => 'core',
        'chatbot' => 'chatbot',

        'university' => 'institution',
        'college' => 'institution',
        'department' => 'institution',
        'user' => 'institution',
        'user_permission' => 'institution',
        'role' => 'institution',
        'system_settings' => 'system',
        'system_health' => 'system',

        'staff' => 'people',
        'student' => 'people',
        'category' => 'people',
        'staff_assignment' => 'attendance',

        'academic_year' => 'academic',
        'programme' => 'academic',
        'semester' => 'academic',
        'subject' => 'academic',
        'curriculum' => 'academic',
        'elective_group' => 'academic',

        'timetable_slot' => 'attendance',
        'lecture' => 'attendance',
        'attendance_summary' => 'attendance',
        'attendance_defaulter' => 'attendance',
        'timetable' => 'attendance',
        'attendance' => 'attendance',

        'exam' => 'exams',
        'exam_subject' => 'exams',
        'grade' => 'exams',
        'marks_entry' => 'exams',
        'result' => 'exams',
        'backlog' => 'exams',
        'promotion' => 'exams',
        'hall_ticket_config' => 'exams',
        'hall_ticket' => 'exams',
        'exam_room' => 'exams',
        'seating' => 'exams',
        'invigilator' => 'exams',
        'practical_schedule' => 'exams',
        'practical_batch' => 'exams',
        'practical_mark' => 'exams',
        'theory_exam' => 'exams',
        'practical_exam' => 'exams',

        'fee_category' => 'fees',
        'fee_structure' => 'fees',
        'student_ledger' => 'fees',
        'fee_collection' => 'fees',
        'receipt' => 'fees',
        'concession' => 'fees',
        'scholarship' => 'fees',
        'fee_report' => 'fees',
        'fees' => 'fees',

        'leave_type' => 'leave',
        'leave_balance' => 'leave',
        'leave_application' => 'leave',
        'leave_approval' => 'leave',
        'leave_cancellation' => 'leave',
        'leave_substitute' => 'leave',
        'holiday' => 'leave',
        'leave' => 'leave',

        'notice_category' => 'notices',
        'notice' => 'notices',
        'notice_audience' => 'notices',
        'notice_attachment' => 'notices',
        'notice_acknowledgement' => 'notices',

        'student_report' => 'reports',
        'attendance_report' => 'reports',
        'result_card' => 'reports',
        'fee_receipt_report' => 'reports',
        'hall_ticket_report' => 'reports',
        'staff_report' => 'reports',
        'activity_log' => 'reports',
        'reports' => 'reports',
        'certificate' => 'certificates',
    ];

    public function canAccessPermission(User $user, string $permissionSlug): bool
    {
        $feature = $this->featureForPermission($permissionSlug);

        return ! $feature || $this->canAccessFeature($user, $feature);
    }

    public function canAccessFeature(User $user, string $feature): bool
    {
        try {
            if ($this->isSuperAdmin($user) || ! $this->licenseColumnsExist()) {
                return true;
            }

            $user->loadMissing('university.licensePlan');
            $university = $user->university;

            if (! $university) {
                return true;
            }

            if (! in_array($university->license_status ?? 'Active', self::ACTIVE_STATUSES, true)) {
                return false;
            }

            if ($university->license_expires_on && Carbon::parse($university->license_expires_on)->lt(today())) {
                return false;
            }

            $plan = $university->licensePlan;

            if (! $plan) {
                return true;
            }

            if (! $plan->is_active) {
                return false;
            }

            $features = $this->normalizeFeatures($plan->features);

            return in_array('*', $features, true) || in_array($feature, $features, true);
        } catch (Throwable $exception) {
            report($exception);

            return true;
        }
    }

    public function featureForPermission(string $permissionSlug): ?string
    {
        [$module] = normalizePermissionParts($permissionSlug);

        foreach (permissionModuleAliases($module) as $alias) {
            if (isset(self::MODULE_FEATURES[$alias])) {
                return self::MODULE_FEATURES[$alias];
            }
        }

        return self::MODULE_FEATURES[$module] ?? null;
    }

    private function licenseColumnsExist(): bool
    {
        return Schema::hasTable('universities')
            && Schema::hasTable('license_plans')
            && Schema::hasColumn('universities', 'license_plan_id')
            && Schema::hasColumn('universities', 'license_status')
            && Schema::hasColumn('universities', 'license_expires_on');
    }

    private function normalizeFeatures(mixed $features): array
    {
        if (is_string($features)) {
            $features = json_decode($features, true) ?: [];
        }

        return collect(is_array($features) ? $features : [])
            ->map(fn ($feature) => trim((string) $feature))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function isSuperAdmin(User $user): bool
    {
        return strcasecmp($user->role?->role_name ?? '', 'Super Admin') === 0;
    }
}
