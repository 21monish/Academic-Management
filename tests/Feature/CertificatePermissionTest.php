<?php

use App\Models\College;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Programme;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

function certificatePermissionStudent(): Student
{
    $university = University::create(['name' => 'Certificate Permission University']);
    $college = College::create([
        'university_id' => $university->university_id,
        'code' => 'CERTC',
        'name' => 'Certificate College',
        'is_active' => true,
    ]);
    $department = Department::create([
        'college_id' => $college->college_id,
        'code' => 'CERTD',
        'name' => 'Certificate Department',
        'is_active' => true,
    ]);
    $programme = Programme::create([
        'dept_id' => $department->dept_id,
        'code' => 'CERTP',
        'name' => 'Certificate Programme',
        'level' => 'UG',
        'duration_semesters' => 8,
        'is_active' => true,
    ]);

    return Student::create([
        'college_id' => $college->college_id,
        'programme_id' => $programme->programme_id,
        'enrollment_no' => 'CERT0001',
        'first_name' => 'Certificate',
        'last_name' => 'Student',
        'gender' => 'Other',
        'dob' => '2005-04-21',
        'student_type' => 'Regular',
        'is_active' => true,
    ]);
}

function certificatePermissionUser(array $permissionSlugs): User
{
    $user = User::factory()->create(['role_id' => null]);
    $permissionIds = collect($permissionSlugs)->map(function (string $slug) {
        [$module, $action] = explode('.', $slug, 2);

        return Permission::where('module_name', $module)
            ->where('action', $action)
            ->value('permission_id');
    })->filter()->all();

    $user->permissions()->sync($permissionIds);

    return $user;
}

test('certificate view permission does not allow certificate printing', function () {
    $this->seed(RolePermissionSeeder::class);
    $student = certificatePermissionStudent();
    $user = certificatePermissionUser(['certificate.view']);

    $this->actingAs($user)
        ->get(route('reports.certificates'))
        ->assertOk()
        ->assertSee('CERT0001')
        ->assertSee('No print permission')
        ->assertDontSee('Bonafide');

    $this->actingAs($user)
        ->get(route('reports.certificates.print', [$student, 'bonafide']))
        ->assertForbidden();
});

test('certificate generate permission allows certificate printing', function () {
    $this->seed(RolePermissionSeeder::class);
    $student = certificatePermissionStudent();
    $user = certificatePermissionUser(['certificate.view', 'certificate.generate']);

    $this->actingAs($user)
        ->get(route('reports.certificates'))
        ->assertOk()
        ->assertSee('Bonafide');

    $this->actingAs($user)
        ->get(route('reports.certificates.print', [$student, 'bonafide']))
        ->assertOk()
        ->assertSee('Bonafide Certificate');
});
