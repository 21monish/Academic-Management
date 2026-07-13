<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Programme;
use App\Models\Staff;
use App\Models\StaffSubjectAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class AccessScopeService
{
    public function forUser(?User $user): array
    {
        if (! $user) {
            return [
                'role' => null,
                'level' => 'guest',
                'university_id' => null,
                'college_id' => null,
                'dept_id' => null,
                'programme_ids' => [],
                'subject_ids' => [],
                'semester_ids' => [],
                'staff_id' => null,
            ];
        }

        $roleName = $user->role?->role_name;
        $staff = $this->staffForUser($user);
        $assignments = $staff
            ? StaffSubjectAssignment::query()
                ->where('staff_id', $staff->staff_id)
                ->where('is_active', true)
                ->get(['subject_id', 'semester_id'])
            : collect();

        $universityId = $user->university_id ? (int) $user->university_id : null;
        $collegeId = $user->college_id ? (int) $user->college_id : ($staff?->college_id ? (int) $staff->college_id : null);
        $deptId = $user->dept_id ? (int) $user->dept_id : ($staff?->dept_id ? (int) $staff->dept_id : null);
        $programmeIds = $user->programme_id ? [(int) $user->programme_id] : [];

        if ($user->programme_id) {
            $programme = Programme::with('department.college')->find($user->programme_id);
            $deptId ??= $programme?->dept_id ? (int) $programme->dept_id : null;
            $collegeId ??= $programme?->department?->college_id ? (int) $programme->department->college_id : null;
            $universityId ??= $programme?->department?->college?->university_id ? (int) $programme->department->college->university_id : null;
        }

        if ($deptId && ! $programmeIds) {
            $programmeIds = Programme::query()
                ->where('dept_id', $deptId)
                ->pluck('programme_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if ($deptId && (! $collegeId || ! $universityId)) {
            $department = Department::with('college')->find($deptId);
            $collegeId ??= $department?->college_id ? (int) $department->college_id : null;
            $universityId ??= $department?->college?->university_id ? (int) $department->college->university_id : null;
        }

        return [
            'role' => $roleName,
            'level' => match (true) {
                ! empty($programmeIds) || filled($deptId) => 'programme',
                filled($collegeId) => 'college',
                filled($universityId) => 'university',
                $roleName === 'Teaching Staff' && ! empty($staff?->staff_id) => 'subject_semester',
                $roleName === 'Super Admin' => 'system',
                in_array($roleName, ['Admin', 'University Admin'], true) => 'university',
                $roleName === 'Principal' => 'college',
                $roleName === 'HOD' => 'programme',
                default => 'own',
            },
            'university_id' => $universityId,
            'college_id' => $collegeId,
            'dept_id' => $deptId,
            'programme_ids' => $programmeIds,
            'subject_ids' => $assignments->pluck('subject_id')->unique()->values()->map(fn ($id) => (int) $id)->all(),
            'semester_ids' => $assignments->pluck('semester_id')->unique()->values()->map(fn ($id) => (int) $id)->all(),
            'staff_id' => $staff?->staff_id ? (int) $staff->staff_id : null,
        ];
    }

    public function applyToStudents(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->where('college_id', $scope['college_id']),
            'programme' => $this->applyProgrammeCollegeFilter($query, $scope)->whereIn('programme_id', $scope['programme_ids'] ?: [0]),
            'subject_semester' => $query->whereHas('enrollments', fn ($enrollment) => $enrollment->whereIn('semester_id', $scope['semester_ids'] ?: [0])),
            default => $query,
        };
    }

    public function applyToUniversities(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return $scope['university_id']
            ? $query->where('university_id', $scope['university_id'])
            : $query;
    }

    public function applyToColleges(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->where('university_id', $scope['university_id']),
            'college', 'programme', 'subject_semester' => $query->where('college_id', $scope['college_id']),
            default => $query,
        };
    }

    public function applyToDepartments(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->where('college_id', $scope['college_id']),
            'programme', 'subject_semester' => $query->where('dept_id', $scope['dept_id']),
            default => $query,
        };
    }

    public function applyToProgrammes(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('department.college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->whereHas('department', fn ($dept) => $dept->where('college_id', $scope['college_id'])),
            'programme' => $query->whereIn('programme_id', $scope['programme_ids'] ?: [0]),
            'subject_semester' => $query->whereIn('programme_id', \App\Models\Semester::query()->whereIn('semester_id', $scope['semester_ids'] ?: [0])->select('programme_id')),
            default => $query,
        };
    }

    public function applyToStaff(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->where('college_id', $scope['college_id']),
            'programme' => $this->applyProgrammeCollegeFilter($query, $scope)->where('dept_id', $scope['dept_id']),
            'subject_semester' => $query->where('staff_id', $scope['staff_id'] ?? 0),
            default => $query,
        };
    }

    public function applyToAssignments(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->where('college_id', $scope['college_id']),
            'programme' => $this->applyProgrammeCollegeFilter($query, $scope)
                ->whereHas('subject', fn ($subject) => $subject->where('dept_id', $scope['dept_id'])),
            'subject_semester' => $query->where('staff_id', $scope['staff_id'] ?? 0),
            default => $query,
        };
    }

    public function applyToAcademicYears(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college', 'programme', 'subject_semester' => $query->where('college_id', $scope['college_id'] ?? 0),
            default => $query,
        };
    }

    public function applyToUsers(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->where('university_id', $scope['university_id']),
            'college' => $query->where('college_id', $scope['college_id']),
            'programme' => $query
                ->when($scope['college_id'], fn ($q, $collegeId) => $q->where('college_id', $collegeId))
                ->when($scope['dept_id'], fn ($q, $deptId) => $q->where('dept_id', $deptId))
                ->when(count($scope['programme_ids']) === 1, fn ($q) => $q->where('programme_id', $scope['programme_ids'][0])),
            'subject_semester' => $query->where('user_id', $user?->user_id ?? 0),
            default => $query,
        };
    }

    public function applyToRoles(Builder $query, ?User $user): Builder
    {
        if (! Schema::hasColumn('user_roles', 'university_id')) {
            return $query;
        }

        $scope = $this->forUser($user);

        if (! Schema::hasColumn('user_roles', 'created_by') || ! $user) {
            return $scope['university_id']
                ? $query->where(function ($roles) use ($scope) {
                    $roles->whereNull('university_id')
                        ->orWhere('university_id', $scope['university_id']);
                })
                : $query;
        }

        return $query->where(function ($roles) use ($scope, $user) {
            $roles->where('is_system_role', true)
                ->orWhere('created_by', $user->user_id)
                ->orWhere(function ($legacyRoles) use ($scope) {
                    $legacyRoles->whereNull('created_by')
                        ->where('is_system_role', false)
                        ->when(
                            $scope['university_id'],
                            fn ($legacyRoles, int $universityId) => $legacyRoles->where('university_id', $universityId),
                            fn ($legacyRoles) => $legacyRoles->whereNull('university_id')
                        );
                });
        });
    }

    public function applyToSlots(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->where('college_id', $scope['college_id']),
            'programme' => $this->applyProgrammeCollegeFilter($query, $scope)
                ->whereHas('semester', fn ($semester) => $semester->whereIn('programme_id', $scope['programme_ids'] ?: [0])),
            'subject_semester' => $query
                ->whereIn('subject_id', $scope['subject_ids'] ?: [0])
                ->whereIn('semester_id', $scope['semester_ids'] ?: [0]),
            default => $query,
        };
    }

    public function applyToSemesters(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('programme.department.college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->whereHas('programme.department', fn ($dept) => $dept->where('college_id', $scope['college_id'])),
            'programme' => $query->whereIn('programme_id', $scope['programme_ids'] ?: [0]),
            'subject_semester' => $query->whereIn('semester_id', $scope['semester_ids'] ?: [0]),
            default => $query,
        };
    }

    public function applyToSubjects(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('department.college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->whereHas('department', fn ($dept) => $dept->where('college_id', $scope['college_id'])),
            'programme' => $query->where('dept_id', $scope['dept_id']),
            'subject_semester' => $query->whereIn('subject_id', $scope['subject_ids'] ?: [0]),
            default => $query,
        };
    }

    public function applyToCurriculum(Builder $query, ?User $user): Builder
    {
        return $query
            ->whereHas('programme', fn ($programme) => $this->applyToProgrammes($programme, $user))
            ->whereHas('semester', fn ($semester) => $this->applyToSemesters($semester, $user))
            ->whereHas('subject', fn ($subject) => $this->applyToSubjects($subject, $user));
    }

    public function applyToLectures(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('slot.college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->whereHas('slot', fn ($slot) => $slot->where('college_id', $scope['college_id'])),
            'programme' => $query->whereHas('slot.semester', fn ($semester) => $semester->whereIn('programme_id', $scope['programme_ids'] ?: [0])),
            'subject_semester' => $query
                ->whereIn('subject_id', $scope['subject_ids'] ?: [0])
                ->whereHas('slot', fn ($slot) => $slot->whereIn('semester_id', $scope['semester_ids'] ?: [0])),
            default => $query,
        };
    }

    public function applyToAttendanceSummaries(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('student.college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->whereHas('student', fn ($student) => $student->where('college_id', $scope['college_id'])),
            'programme' => $query->whereHas('student', fn ($student) => $student->whereIn('programme_id', $scope['programme_ids'] ?: [0])),
            'subject_semester' => $query
                ->whereIn('subject_id', $scope['subject_ids'] ?: [0])
                ->whereIn('semester_id', $scope['semester_ids'] ?: [0]),
            default => $query,
        };
    }

    public function applyToFeeStructures(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->where('college_id', $scope['college_id']),
            'programme' => $this->applyProgrammeCollegeFilter($query, $scope)->whereIn('programme_id', $scope['programme_ids'] ?: [0]),
            'subject_semester' => $query->whereIn('semester_id', $scope['semester_ids'] ?: [0]),
            default => $query,
        };
    }

    public function applyToFeeLedgers(Builder $query, ?User $user): Builder
    {
        return $query->whereHas('student', fn ($student) => $this->applyToStudents($student, $user));
    }

    public function applyToFeePayments(Builder $query, ?User $user): Builder
    {
        return $query->whereHas('student', fn ($student) => $this->applyToStudents($student, $user));
    }

    public function applyToExams(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university' => $query->whereHas('college', fn ($college) => $college->where('university_id', $scope['university_id'])),
            'college' => $query->where('college_id', $scope['college_id']),
            'programme' => $this->applyProgrammeCollegeFilter($query, $scope)
                ->whereHas('semester', fn ($semester) => $semester->whereIn('programme_id', $scope['programme_ids'] ?: [0])),
            'subject_semester' => $query->whereIn('semester_id', $scope['semester_ids'] ?: [0]),
            default => $query,
        };
    }

    public function applyToExamSubjects(Builder $query, ?User $user): Builder
    {
        $scope = $this->forUser($user);

        return match ($scope['level']) {
            'university', 'college', 'programme' => $query->whereHas('exam', fn ($exam) => $this->applyToExams($exam, $user)),
            'subject_semester' => $query
                ->whereIn('subject_id', $scope['subject_ids'] ?: [0])
                ->whereHas('exam', fn ($exam) => $exam->whereIn('semester_id', $scope['semester_ids'] ?: [0])),
            default => $query,
        };
    }

    public function applyToResults(Builder $query, ?User $user): Builder
    {
        return $query->whereHas('student', fn ($student) => $this->applyToStudents($student, $user))
            ->whereHas('examSubject', fn ($examSubject) => $this->applyToExamSubjects($examSubject, $user));
    }

    public function applyToSemesterResultSummaries(Builder $query, ?User $user): Builder
    {
        return $query->whereHas('student', fn ($student) => $this->applyToStudents($student, $user))
            ->whereHas('exam', fn ($exam) => $this->applyToExams($exam, $user));
    }

    public function applyToBacklogs(Builder $query, ?User $user): Builder
    {
        return $query->whereHas('student', fn ($student) => $this->applyToStudents($student, $user))
            ->whereHas('subject', fn ($subject) => $this->applyToSubjects($subject, $user))
            ->whereHas('semester', fn ($semester) => $this->applyToSemesters($semester, $user));
    }

    public function applyToHallTickets(Builder $query, ?User $user): Builder
    {
        return $query->whereHas('student', fn ($student) => $this->applyToStudents($student, $user));
    }

    public function staffForUser(User $user): ?Staff
    {
        if ($user->reference_type === 'Staff' && $user->reference_id) {
            return Staff::find($user->reference_id);
        }

        return Staff::query()
            ->where('email', $user->email)
            ->first();
    }

    private function applyProgrammeCollegeFilter(Builder $query, array $scope): Builder
    {
        return $scope['college_id']
            ? $query->where('college_id', $scope['college_id'])
            : $query;
    }
}
