<?php

namespace App\Services;

use App\Models\College;
use App\Models\Department;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class DataIntegrityService
{
    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function lockStaffData(array $data, Request $request): array
    {
        $user = $request->user();
        $scope = $this->accessScope->forUser($user);

        if ($scope['college_id']) {
            $data['college_id'] = $scope['college_id'];
        }

        if ($scope['dept_id']) {
            $data['dept_id'] = $scope['dept_id'];
        }

        abort_unless($this->accessScope->applyToColleges(College::whereKey($data['college_id'] ?? 0), $user)->exists(), 403);

        if (! empty($data['dept_id'])) {
            abort_unless($this->accessScope->applyToDepartments(Department::whereKey($data['dept_id']), $user)->exists(), 403);

            $department = Department::query()->find($data['dept_id']);
            $data['college_id'] = $department?->college_id;
        }

        return $data;
    }

    public function lockStudentData(array $data, Request $request): array
    {
        $user = $request->user();
        $scope = $this->accessScope->forUser($user);

        if (count($scope['programme_ids']) === 1) {
            $data['programme_id'] = $scope['programme_ids'][0];
        }

        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($data['programme_id'] ?? 0), $user)->exists(), 403);

        $programme = Programme::query()
            ->with('department.college')
            ->find($data['programme_id']);

        abort_unless($programme && $programme->department && $programme->department->college, 422);

        $data['programme_id'] = $programme->programme_id;
        $data['college_id'] = $programme->department->college_id;

        if ($scope['college_id'] && (int) $data['college_id'] !== (int) $scope['college_id']) {
            abort(403);
        }

        if ($scope['dept_id'] && (int) $programme->dept_id !== (int) $scope['dept_id']) {
            abort(403);
        }

        return $data;
    }

    public function lockSubjectData(array $data, Request $request): array
    {
        $user = $request->user();
        $scope = $this->accessScope->forUser($user);

        if (count($scope['programme_ids']) === 1) {
            $data['programme_id'] = $scope['programme_ids'][0];
        }

        abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($data['programme_id'] ?? 0), $user)->exists(), 403);
        abort_unless($this->accessScope->applyToSemesters(
            Semester::whereKey($data['semester_id'] ?? 0)->where('programme_id', $data['programme_id'] ?? 0),
            $user
        )->exists(), 403);

        $programme = Programme::query()
            ->with('department.college')
            ->find($data['programme_id']);

        abort_unless($programme && $programme->department && $programme->department->college, 422);

        $data['department_id'] = $programme->dept_id;

        if ($scope['college_id'] && (int) $programme->department->college_id !== (int) $scope['college_id']) {
            abort(403);
        }

        if ($scope['dept_id'] && (int) $programme->dept_id !== (int) $scope['dept_id']) {
            abort(403);
        }

        return $data;
    }

    public function protectUniversityDelete(University $university): void
    {
        $this->guardDelete('university', [
            ['colleges', 'university_id', $university->university_id, 'colleges'],
            ['users', 'university_id', $university->university_id, 'users'],
            ['user_roles', 'university_id', $university->university_id, 'roles'],
        ]);
    }

    public function protectCollegeDelete(College $college): void
    {
        $this->guardDelete('college', [
            ['departments', 'college_id', $college->college_id, 'departments'],
            ['staff', 'college_id', $college->college_id, 'staff'],
            ['students', 'college_id', $college->college_id, 'students'],
            ['users', 'college_id', $college->college_id, 'users'],
            ['academic_years', 'college_id', $college->college_id, 'academic years'],
            ['staff_subject_assignments', 'college_id', $college->college_id, 'subject assignments'],
            ['timetable_slots', 'college_id', $college->college_id, 'timetable slots'],
            ['exams', 'college_id', $college->college_id, 'exams'],
            ['notices', 'college_id', $college->college_id, 'notices'],
            ['fee_structures', 'college_id', $college->college_id, 'fee structures'],
        ]);
    }

    public function protectDepartmentDelete(Department $department): void
    {
        $this->guardDelete('department', [
            ['programmes', 'dept_id', $department->dept_id, 'programmes'],
            ['subjects', 'dept_id', $department->dept_id, 'subjects'],
            ['staff', 'dept_id', $department->dept_id, 'staff'],
            ['users', 'dept_id', $department->dept_id, 'users'],
            ['notices', 'dept_id', $department->dept_id, 'notices'],
            ['practical_exam_schedules', 'dept_id', $department->dept_id, 'practical exam schedules'],
        ]);
    }

    public function protectProgrammeDelete(Programme $programme): void
    {
        $this->guardDelete('programme', [
            ['semesters', 'programme_id', $programme->programme_id, 'semesters'],
            ['curriculum', 'programme_id', $programme->programme_id, 'curriculum'],
            ['students', 'programme_id', $programme->programme_id, 'students'],
            ['users', 'programme_id', $programme->programme_id, 'users'],
            ['grade_master', 'programme_id', $programme->programme_id, 'grades'],
            ['promotion_rules', 'programme_id', $programme->programme_id, 'promotion rules'],
            ['fee_structures', 'programme_id', $programme->programme_id, 'fee structures'],
        ]);
    }

    public function protectSemesterDelete(Semester $semester): void
    {
        $this->guardDelete('semester', [
            ['curriculum', 'semester_id', $semester->semester_id, 'curriculum'],
            ['academic_year_semesters', 'semester_id', $semester->semester_id, 'academic year mappings'],
            ['student_enrollments', 'semester_id', $semester->semester_id, 'student enrollments'],
            ['timetable_slots', 'semester_id', $semester->semester_id, 'timetable slots'],
            ['staff_subject_assignments', 'semester_id', $semester->semester_id, 'subject assignments'],
            ['exams', 'semester_id', $semester->semester_id, 'exams'],
            ['attendance_summaries', 'semester_id', $semester->semester_id, 'attendance summaries'],
            ['fee_structures', 'semester_id', $semester->semester_id, 'fee structures'],
            ['student_fee_ledgers', 'semester_id', $semester->semester_id, 'student fee ledgers'],
            ['backlogs', 'semester_id', $semester->semester_id, 'backlogs'],
            ['semester_result_summaries', 'semester_id', $semester->semester_id, 'semester results'],
            ['student_promotions', 'from_semester_id', $semester->semester_id, 'student promotions'],
            ['student_promotions', 'to_semester_id', $semester->semester_id, 'student promotions'],
        ]);
    }

    public function protectSubjectDelete(Subject $subject): void
    {
        $this->guardDelete('subject', [
            ['curriculum', 'subject_id', $subject->subject_id, 'curriculum'],
            ['staff_subject_assignments', 'subject_id', $subject->subject_id, 'subject assignments'],
            ['timetable_slots', 'subject_id', $subject->subject_id, 'timetable slots'],
            ['lectures', 'subject_id', $subject->subject_id, 'lectures'],
            ['attendance_summaries', 'subject_id', $subject->subject_id, 'attendance summaries'],
            ['student_elective_choices', 'subject_id', $subject->subject_id, 'student elective choices'],
            ['exam_subjects', 'subject_id', $subject->subject_id, 'exam subjects'],
            ['backlogs', 'subject_id', $subject->subject_id, 'backlogs'],
            ['theory_exam_schedules', 'subject_id', $subject->subject_id, 'theory exam schedules'],
            ['practical_exam_schedules', 'subject_id', $subject->subject_id, 'practical exam schedules'],
            ['leave_substitutes', 'subject_id', $subject->subject_id, 'leave substitutes'],
            ['practical_marks', 'subject_id', $subject->subject_id, 'practical marks'],
            ['hall_ticket_subjects', 'subject_id', $subject->subject_id, 'hall ticket subjects'],
        ]);
    }

    public function protectSelfAccountUpdate(User $targetUser, Request $request, ?array $permissionIds = null): void
    {
        if (! $request->user()?->is($targetUser)) {
            return;
        }

        if ($request->has('is_active') && ! $request->boolean('is_active')) {
            throw ValidationException::withMessages([
                'is_active' => 'You cannot deactivate your own account.',
            ]);
        }

        if ($permissionIds !== null) {
            $this->protectSelfPermissionUpdater($permissionIds);
        }
    }

    public function protectSelfPermissionUpdater(array $permissionIds): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $requiredIds = DB::table('permissions')
            ->where('module_name', 'user_permission')
            ->whereIn('action', ['view', 'update'])
            ->pluck('permission_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $missing = array_diff($requiredIds, $permissionIds);

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'permissions' => 'You cannot remove your own permission updater access.',
            ]);
        }
    }

    private function guardDelete(string $recordLabel, array $checks): void
    {
        foreach ($checks as [$table, $column, $value, $dependencyLabel]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            if (DB::table($table)->where($column, $value)->exists()) {
                throw ValidationException::withMessages([
                    'delete' => "Cannot delete this {$recordLabel} because it is used by {$dependencyLabel}.",
                ]);
            }
        }
    }
}
