<?php

use App\Models\College;
use App\Models\Department;
use App\Models\Programme;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AccessScopeService;
use Database\Seeders\RolePermissionSeeder;

function accessScopeFixtures(): array
{
    $gtu = University::create(['name' => 'GTU Scope University']);
    $otherUniversity = University::create(['name' => 'Other Scope University']);

    $gtuCollege = College::create([
        'university_id' => $gtu->university_id,
        'code' => 'SCGTU1',
        'name' => 'GTU Scope College',
        'is_active' => true,
    ]);
    $otherGtuCollege = College::create([
        'university_id' => $gtu->university_id,
        'code' => 'SCGTU2',
        'name' => 'Other GTU Scope College',
        'is_active' => true,
    ]);
    $otherCollege = College::create([
        'university_id' => $otherUniversity->university_id,
        'code' => 'SCOTH1',
        'name' => 'Other Scope College',
        'is_active' => true,
    ]);

    $ceDept = Department::create([
        'college_id' => $gtuCollege->college_id,
        'code' => 'SC_CE',
        'name' => 'Scope Computer Engineering',
        'is_active' => true,
    ]);
    $meDept = Department::create([
        'college_id' => $gtuCollege->college_id,
        'code' => 'SC_ME',
        'name' => 'Scope Mechanical Engineering',
        'is_active' => true,
    ]);

    $ceProgramme = Programme::create([
        'dept_id' => $ceDept->dept_id,
        'code' => 'SC_BE_CE',
        'name' => 'Scope BE Computer',
        'level' => 'UG',
        'is_active' => true,
    ]);
    $itProgramme = Programme::create([
        'dept_id' => $ceDept->dept_id,
        'code' => 'SC_BE_IT',
        'name' => 'Scope BE IT',
        'level' => 'UG',
        'is_active' => true,
    ]);
    $meProgramme = Programme::create([
        'dept_id' => $meDept->dept_id,
        'code' => 'SC_BE_ME',
        'name' => 'Scope BE Mechanical',
        'level' => 'UG',
        'is_active' => true,
    ]);

    Student::create([
        'college_id' => $gtuCollege->college_id,
        'programme_id' => $ceProgramme->programme_id,
        'enrollment_no' => 'SCOPE_CE_001',
        'first_name' => 'Scope',
        'last_name' => 'Computer',
        'is_active' => true,
    ]);
    Student::create([
        'college_id' => $gtuCollege->college_id,
        'programme_id' => $itProgramme->programme_id,
        'enrollment_no' => 'SCOPE_IT_001',
        'first_name' => 'Scope',
        'last_name' => 'IT',
        'is_active' => true,
    ]);
    Student::create([
        'college_id' => $gtuCollege->college_id,
        'programme_id' => $meProgramme->programme_id,
        'enrollment_no' => 'SCOPE_ME_001',
        'first_name' => 'Scope',
        'last_name' => 'Mechanical',
        'is_active' => true,
    ]);
    Student::create([
        'college_id' => $otherGtuCollege->college_id,
        'programme_id' => $ceProgramme->programme_id,
        'enrollment_no' => 'SCOPE_OTHER_GTU_001',
        'first_name' => 'Scope',
        'last_name' => 'Other GTU',
        'is_active' => true,
    ]);
    Student::create([
        'college_id' => $otherCollege->college_id,
        'programme_id' => $ceProgramme->programme_id,
        'enrollment_no' => 'SCOPE_OTHER_001',
        'first_name' => 'Scope',
        'last_name' => 'Other',
        'is_active' => true,
    ]);

    return compact('gtu', 'gtuCollege', 'ceDept', 'ceProgramme');
}

function scopedAdminUser(array $attributes): User
{
    $role = UserRole::where('role_name', 'Admin')->firstOrFail();

    return User::factory()->create([
        'role_id' => $role->role_id,
        ...$attributes,
    ]);
}

test('user with university id is filtered to that university', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = accessScopeFixtures();
    $user = scopedAdminUser(['university_id' => $fixtures['gtu']->university_id]);
    $service = app(AccessScopeService::class);

    expect($service->forUser($user)['level'])->toBe('university');

    $enrollments = $service->applyToStudents(Student::query(), $user)
        ->pluck('enrollment_no')
        ->all();

    expect($enrollments)->toContain('SCOPE_CE_001', 'SCOPE_OTHER_GTU_001')
        ->not->toContain('SCOPE_OTHER_001');
});

test('user with university and college id is filtered to that college', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = accessScopeFixtures();
    $user = scopedAdminUser([
        'university_id' => $fixtures['gtu']->university_id,
        'college_id' => $fixtures['gtuCollege']->college_id,
    ]);
    $service = app(AccessScopeService::class);

    expect($service->forUser($user)['level'])->toBe('college');

    $enrollments = $service->applyToStudents(Student::query(), $user)
        ->pluck('enrollment_no')
        ->all();

    expect($enrollments)->toContain('SCOPE_CE_001', 'SCOPE_IT_001', 'SCOPE_ME_001')
        ->not->toContain('SCOPE_OTHER_GTU_001', 'SCOPE_OTHER_001');
});

test('user with department id is filtered to department programmes', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = accessScopeFixtures();
    $user = scopedAdminUser([
        'university_id' => $fixtures['gtu']->university_id,
        'college_id' => $fixtures['gtuCollege']->college_id,
        'dept_id' => $fixtures['ceDept']->dept_id,
    ]);
    $service = app(AccessScopeService::class);

    $scope = $service->forUser($user);

    expect($scope['level'])->toBe('programme')
        ->and($scope['programme_ids'])->toHaveCount(2);

    $enrollments = $service->applyToStudents(Student::query(), $user)
        ->pluck('enrollment_no')
        ->all();

    expect($enrollments)->toContain('SCOPE_CE_001', 'SCOPE_IT_001')
        ->not->toContain('SCOPE_ME_001');
});

test('user with programme id is filtered to that programme', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = accessScopeFixtures();
    $user = scopedAdminUser([
        'university_id' => $fixtures['gtu']->university_id,
        'college_id' => $fixtures['gtuCollege']->college_id,
        'dept_id' => $fixtures['ceDept']->dept_id,
        'programme_id' => $fixtures['ceProgramme']->programme_id,
    ]);
    $service = app(AccessScopeService::class);

    $scope = $service->forUser($user);

    expect($scope['level'])->toBe('programme')
        ->and($scope['programme_ids'])->toBe([(int) $fixtures['ceProgramme']->programme_id]);

    $enrollments = $service->applyToStudents(Student::query(), $user)
        ->pluck('enrollment_no')
        ->all();

    expect($enrollments)->toBe(['SCOPE_CE_001']);
});
