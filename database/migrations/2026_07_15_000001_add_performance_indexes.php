<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, array{0: string, 1: array<int, string>, 2: string}>
     */
    private array $indexes = [
        ['users', ['university_id', 'college_id', 'dept_id', 'programme_id'], 'users_scope_idx'],
        ['users', ['reference_type', 'reference_id'], 'users_ref_idx'],
        ['users', ['role_id', 'is_active'], 'users_role_active_idx'],

        ['colleges', ['university_id', 'is_active'], 'colleges_univ_active_idx'],
        ['departments', ['college_id', 'is_active'], 'departments_college_active_idx'],
        ['programmes', ['dept_id', 'is_active'], 'programmes_dept_active_idx'],
        ['semesters', ['programme_id', 'semester_no', 'academic_year'], 'semesters_prog_no_year_idx'],
        ['subjects', ['dept_id', 'is_active'], 'subjects_dept_active_idx'],
        ['curriculum', ['semester_id', 'subject_id'], 'curriculum_sem_subject_idx'],
        ['academic_years', ['college_id', 'is_current'], 'acad_year_col_current_idx'],

        ['staff', ['college_id', 'dept_id', 'staff_type', 'is_active'], 'staff_scope_idx'],
        ['students', ['college_id', 'programme_id', 'is_active'], 'students_scope_idx'],
        ['students', ['student_type', 'admission_type'], 'students_type_idx'],
        ['student_enrollments', ['semester_id', 'academic_year_id', 'status'], 'enroll_sem_year_status_idx'],

        ['staff_subject_assignments', ['staff_id', 'semester_id', 'is_active'], 'assign_staff_sem_active_idx'],
        ['staff_subject_assignments', ['college_id', 'subject_id', 'semester_id'], 'assign_col_sub_sem_idx'],
        ['timetable_slots', ['college_id', 'semester_id', 'is_active'], 'slots_col_sem_active_idx'],
        ['timetable_slots', ['staff_id', 'day_of_week', 'is_active'], 'slots_staff_day_active_idx'],
        ['lectures', ['staff_id', 'lecture_date'], 'lectures_staff_date_idx'],
        ['lectures', ['subject_id', 'lecture_date'], 'lectures_subject_date_idx'],
        ['attendances', ['student_id', 'status'], 'attendances_student_status_idx'],
        ['attendance_summaries', ['semester_id', 'attendance_percentage'], 'att_sum_sem_pct_idx'],
        ['attendance_summaries', ['subject_id', 'attendance_percentage'], 'att_sum_subject_pct_idx'],

        ['exams', ['college_id', 'semester_id', 'academic_year_id'], 'exams_scope_idx'],
        ['exams', ['is_published', 'created_at'], 'exams_pub_created_idx'],
        ['exam_subjects', ['subject_id', 'exam_date'], 'exam_subject_date_idx'],
        ['results', ['student_id', 'is_published'], 'results_student_pub_idx'],
        ['results', ['result_status', 'is_published'], 'results_status_pub_idx'],
        ['semester_result_summaries', ['semester_id', 'is_published'], 'sem_results_sem_pub_idx'],
        ['hall_tickets', ['student_id', 'status', 'generated'], 'hall_student_status_idx'],
        ['hall_tickets', ['generated', 'generated_at'], 'hall_generated_at_idx'],

        ['student_fee_ledgers', ['student_id', 'payment_status'], 'ledgers_student_status_idx'],
        ['student_fee_ledgers', ['academic_year_id', 'semester_id', 'payment_status'], 'ledgers_year_sem_status_idx'],
        ['fee_payments', ['student_id', 'payment_status'], 'payments_student_status_idx'],
        ['fee_payments', ['payment_status', 'payment_date'], 'payments_status_date_idx'],

        ['notices', ['college_id', 'dept_id', 'is_published'], 'notices_scope_pub_idx'],
        ['notices', ['is_published', 'published_at'], 'notices_pub_at_idx'],
        ['leave_applications', ['staff_id', 'status'], 'leave_staff_status_idx'],
        ['leave_applications', ['applied_to_staff_id', 'status'], 'leave_approver_status_idx'],
        ['leave_applications', ['academic_year_id', 'status'], 'leave_year_status_idx'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as [$table, $columns, $name]) {
            $this->addIndex($table, $columns, $name);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes) as [$table, , $name]) {
            $this->dropIndex($table, $name);
        }
    }

    /**
     * @param array<int, string> $columns
     */
    private function addIndex(string $tableName, array $columns, string $indexName): void
    {
        if (! $this->tableHasColumns($tableName, $columns) || $this->hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndex(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    /**
     * @param array<int, string> $columns
     */
    private function tableHasColumns(string $tableName, array $columns): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        try {
            if (! method_exists(Schema::getFacadeRoot(), 'getIndexes')) {
                return false;
            }

            foreach (Schema::getIndexes($tableName) as $index) {
                if (($index['name'] ?? null) === $indexName) {
                    return true;
                }
            }
        } catch (Throwable) {
            return false;
        }

        return false;
    }
};
