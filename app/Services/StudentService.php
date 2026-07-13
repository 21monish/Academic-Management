<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class StudentService
{
    public function __construct(
        protected DatabaseManager $db,
        protected UploadService $uploads,
        protected AccessScopeService $accessScope
    )
    {
    }

    /**
     * @return array{0: \Illuminate\Pagination\LengthAwarePaginator, 1: array}
     */
    public function searchAndFilter(Request $request): array
    {
        $query = $this->accessScope
            ->applyToStudents(Student::query()->with(['college', 'programme', 'category']), $request->user());

        $filters = [
            'q' => $request->string('q')->toString(),
            'college_id' => $request->string('college_id')->toString(),
            'programme_id' => $request->string('programme_id')->toString(),
            'category_id' => $request->string('category_id')->toString(),
            'gender' => $request->string('gender')->toString(),
            'admission_type' => $request->string('admission_type')->toString(),
            'is_active' => $request->string('is_active')->toString(),
            'sort' => $request->string('sort')->toString(),
            'direction' => $request->string('direction')->toString(),
        ];

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($sub) use ($q) {
                $sub->where('enrollment_no', 'like', '%' . $q . '%')
                    ->orWhere('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ['%' . $q . '%'])
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('phone', 'like', '%' . $q . '%')
                    ->orWhere('guardian_name', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('college_id')) {
            $query->where('college_id', $request->string('college_id')->toString());
        }

        if ($request->filled('programme_id')) {
            $query->where('programme_id', $request->string('programme_id')->toString());
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->string('category_id')->toString());
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->string('gender')->toString());
        }

        if ($request->filled('admission_type')) {
            $query->where('admission_type', $request->string('admission_type')->toString());
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $allowedSort = ['student_id', 'enrollment_no', 'first_name', 'last_name', 'email', 'phone', 'is_active'];
        $sort = in_array($request->string('sort')->toString(), $allowedSort, true)
            ? $request->string('sort')->toString()
            : 'student_id';

        $direction = $request->string('direction')->toString();
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $students = $query->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return [$students, $filters];
    }

    public function create(array $data, Request $request): Student
    {
        return $this->db->transaction(function () use ($data, $request) {
            $this->authorizeStudentData($data, $request);

            $isActive = $data['is_active'] ?? true;
            $photoUrl = $request->hasFile('photo')
                ? $this->uploads->storePublicUpload($request->file('photo'), 'uploads/photos')
                : null;

            $student = Student::query()->create([
                'college_id' => $data['college_id'],
                'programme_id' => $data['programme_id'],
                'category_id' => $data['category_id'] ?? null,

                'enrollment_no' => $data['enrollment_no'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'] ?? null,
                'dob' => $data['dob'] ?? null,

                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,

                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,

                'photo_url' => $photoUrl,

                'admission_date' => $data['admission_date'] ?? null,
                'admission_type' => $data['admission_type'] ?? null,

                'is_active' => (bool) $isActive,
            ]);

            $this->syncUserAccount($student, true);

            return $student;
        });
    }

    public function update(Student $student, array $data, Request $request): void
    {
        $this->db->transaction(function () use ($student, $data, $request) {
            abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), $request->user())->exists(), 403);
            $this->authorizeStudentData($data, $request);

            $isActive = $data['is_active'] ?? $student->is_active ?? true;
            $photoUrl = $request->hasFile('photo')
                ? $this->uploads->storePublicUpload($request->file('photo'), 'uploads/photos')
                : $student->photo_url;

            $student->update([
                'college_id' => $data['college_id'],
                'programme_id' => $data['programme_id'],
                'category_id' => $data['category_id'] ?? null,

                'enrollment_no' => $data['enrollment_no'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'] ?? null,
                'dob' => $data['dob'] ?? null,

                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,

                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,

                'photo_url' => $photoUrl,

                'admission_date' => $data['admission_date'] ?? null,
                'admission_type' => $data['admission_type'] ?? null,

                'is_active' => (bool) $isActive,
            ]);

            $this->syncUserAccount($student, false);
        });
    }

    public function delete(Student $student): void
    {
        $this->db->transaction(function () use ($student) {
            abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), request()->user())->exists(), 403);

            User::query()
                ->where('reference_type', 'Student')
                ->where('reference_id', $student->student_id)
                ->each(fn (User $user) => $user->delete());

            $student->delete();
        });
    }

    public function setActive(Student $student, bool $active): void
    {
        $this->db->transaction(function () use ($student, $active) {
            abort_unless($this->accessScope->applyToStudents(Student::whereKey($student->student_id), request()->user())->exists(), 403);

            $student->update(['is_active' => $active]);
            $this->syncUserAccount($student, false);
        });
    }

    private function authorizeStudentData(array $data, Request $request): void
    {
        abort_unless($this->accessScope->applyToColleges(\App\Models\College::whereKey($data['college_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToProgrammes(\App\Models\Programme::whereKey($data['programme_id']), $request->user())->exists(), 403);
    }

    private function syncUserAccount(Student $student, bool $autoCreate): void
    {
        $student->loadMissing('college', 'programme.department.college');

        $hierarchy = [
            'reference_type' => 'Student',
            'reference_id' => $student->student_id,
            'university_id' => $student->programme?->department?->college?->university_id ?? $student->college?->university_id,
            'college_id' => $student->college_id,
            'dept_id' => $student->programme?->dept_id,
            'programme_id' => $student->programme_id,
        ];

        $studentRole = UserRole::query()->where('role_name', 'Student')->first();
        $linkedUser = User::query()
            ->where('reference_type', 'Student')
            ->where('reference_id', $student->student_id)
            ->first();

        if (! $linkedUser && $autoCreate) {

            $user = User::query()->create($hierarchy + [
                'role_id' => $studentRole?->role_id,
                'username' => $student->enrollment_no,
                'email' => $this->studentLoginEmail($student),
                'password_hash' => Hash::make($student->dob ? Carbon::parse($student->dob)->format('dmY') : $student->enrollment_no),
                'phone' => $student->phone,
                'is_active' => (bool) $student->is_active,
                'is_verified' => true,
                'must_change_password' => true,
            ]);

            $this->syncRolePermissions($user);

            return;
        }

        if (! $linkedUser) {
            return;
        }

        $linkedUser->update($hierarchy + [
            'role_id' => $studentRole?->role_id,
            'username' => $student->enrollment_no,
            'email' => $this->studentLoginEmail($student, $linkedUser->user_id),
            'phone' => $student->phone,
            'is_active' => (bool) $student->is_active,
        ]);
    }

    private function studentLoginEmail(Student $student, ?int $ignoreUserId = null): string
    {
        if ($student->email && ! User::query()
            ->where('email', $student->email)
            ->when($ignoreUserId, fn ($query, int $userId) => $query->whereKeyNot($userId))
            ->exists()) {
            return $student->email;
        }

        $base = Str::lower(preg_replace('/[^A-Za-z0-9._-]/', '', $student->enrollment_no)) ?: 'student'.$student->student_id;
        $email = $base.'@student.local';
        $counter = 1;

        while (User::query()
            ->where('email', $email)
            ->when($ignoreUserId, fn ($query, int $userId) => $query->whereKeyNot($userId))
            ->exists()) {
            $email = $base.$counter.'@student.local';
            $counter++;
        }

        return $email;
    }

    private function syncRolePermissions(User $user): void
    {
        $permissionIds = $user->role?->permissions()
            ->pluck('permissions.permission_id')
            ->all() ?? [];

        $user->permissions()->sync($permissionIds);
    }

}

