<?php

use App\Models\College;
use App\Models\Department;
use App\Models\Notice;
use App\Models\Permission;
use App\Models\Programme;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;

function userWithSeededRole(string $roleName, array $attributes = []): User
{
    $role = $roleName === 'Super Admin'
        ? UserRole::where('role_name', 'Super Admin')->first()
        : null;

    $user = User::factory()->create($attributes + ['role_id' => $role?->role_id]);
    $permissions = testPermissionsForProfile($roleName);

    if ($roleName !== 'Super Admin' && $permissions) {
        $user->permissions()->sync(collect($permissions)->map(function (array $permission) {
            return Permission::where('module_name', $permission[0])
                ->where('action', $permission[1])
                ->value('permission_id');
        })->filter()->all());
    }

    return $user;
}

function testPermissionsForProfile(string $profile): array
{
    return match ($profile) {
        'Student' => [
            ['notice', 'view'],
        ],
        'HOD' => [
            ['notice', 'view'],
            ['notice', 'create'],
            ['student', 'view'],
        ],
        'Principal' => [
            ['student', 'view'],
        ],
        default => [],
    };
}

test('web responses include defensive security headers', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
});

test('student role is blocked from direct admin module URLs', function () {
    $this->seed(RolePermissionSeeder::class);
    $student = userWithSeededRole('Student');

    $this->actingAs($student)->get(route('students.index'))->assertForbidden();
    $this->actingAs($student)->get(route('fees.receipts'))->assertForbidden();
    $this->actingAs($student)->get(route('reports.students'))->assertForbidden();
    $this->actingAs($student)->get(route('notices.index'))->assertOk();
});

test('student notice page only shows published viewer content', function () {
    $this->seed(RolePermissionSeeder::class);

    $student = userWithSeededRole('Student');
    $admin = userWithSeededRole('Super Admin');
    $university = University::create([
        'name' => 'GTU',
    ]);
    $college = College::create([
        'university_id' => $university->university_id,
        'code' => 'ITR',
        'name' => 'ITR College',
        'is_active' => true,
    ]);

    Notice::create([
        'college_id' => $college->college_id,
        'created_by' => $admin->user_id,
        'title' => 'Published exam notice',
        'priority' => 'Normal',
        'audience_type' => 'All',
        'is_published' => true,
        'published_at' => now(),
    ]);

    Notice::create([
        'college_id' => $college->college_id,
        'created_by' => $admin->user_id,
        'title' => 'Draft admin notice',
        'priority' => 'Normal',
        'audience_type' => 'All',
        'is_published' => false,
    ]);

    $this->actingAs($student)
        ->get(route('notices.index'))
        ->assertOk()
        ->assertSee('Published exam notice')
        ->assertDontSee('Draft admin notice')
        ->assertDontSee('Save Notice')
        ->assertDontSee('Delete');

    $this->actingAs($student)
        ->get(route('notices.index', ['q' => 'Draft']))
        ->assertOk()
        ->assertDontSee('Draft admin notice');
});

test('hod notices are scoped to their own college', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = userWithSeededRole('Super Admin');
    $university = University::create([
        'name' => 'GTU',
    ]);
    $ldrp = College::create([
        'university_id' => $university->university_id,
        'code' => 'LDRP',
        'name' => 'LDRP Institute',
        'is_active' => true,
    ]);
    $bvm = College::create([
        'university_id' => $university->university_id,
        'code' => 'BVM',
        'name' => 'BVM College',
        'is_active' => true,
    ]);
    $ldrpDept = Department::create([
        'college_id' => $ldrp->college_id,
        'code' => 'CE',
        'name' => 'Computer Engineering',
        'is_active' => true,
    ]);
    $bvmDept = Department::create([
        'college_id' => $bvm->college_id,
        'code' => 'IT',
        'name' => 'Information Technology',
        'is_active' => true,
    ]);
    $hod = userWithSeededRole('HOD', [
        'university_id' => $university->university_id,
        'college_id' => $ldrp->college_id,
        'dept_id' => $ldrpDept->dept_id,
    ]);

    Notice::create([
        'college_id' => $ldrp->college_id,
        'dept_id' => $ldrpDept->dept_id,
        'created_by' => $admin->user_id,
        'title' => 'LDRP department notice',
        'priority' => 'Normal',
        'audience_type' => 'Dept',
        'is_published' => true,
        'published_at' => now(),
    ]);

    Notice::create([
        'college_id' => $bvm->college_id,
        'dept_id' => $bvmDept->dept_id,
        'created_by' => $admin->user_id,
        'title' => 'BVM department notice',
        'priority' => 'Normal',
        'audience_type' => 'Dept',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($hod)
        ->get(route('notices.index'))
        ->assertOk()
        ->assertSee('LDRP department notice')
        ->assertSee('LDRP Institute')
        ->assertSee('Computer Engineering')
        ->assertDontSee('BVM department notice');

    $this->actingAs($hod)
        ->post(route('notices.store'), [
            'college_id' => $bvm->college_id,
            'dept_id' => $bvmDept->dept_id,
            'title' => 'Spoofed college notice',
            'priority' => 'Normal',
            'audience_type' => 'Dept',
            'is_published' => true,
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('notices', [
        'title' => 'Spoofed college notice',
        'college_id' => $ldrp->college_id,
        'dept_id' => $ldrpDept->dept_id,
    ]);
    $this->assertDatabaseMissing('notices', [
        'title' => 'Spoofed college notice',
        'college_id' => $bvm->college_id,
        'dept_id' => $bvmDept->dept_id,
    ]);
});

test('authenticated session stores hierarchy access scope', function () {
    $this->seed(RolePermissionSeeder::class);

    $university = University::create(['name' => 'GTU']);
    $college = College::create([
        'university_id' => $university->university_id,
        'code' => 'LDRP',
        'name' => 'LDRP Institute',
        'is_active' => true,
    ]);
    $principal = userWithSeededRole('Principal', [
        'university_id' => $university->university_id,
        'college_id' => $college->college_id,
    ]);

    $this->actingAs($principal)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionHas('access_scope.level', 'college')
        ->assertSessionHas('access_scope.university_id', $university->university_id)
        ->assertSessionHas('access_scope.college_id', $college->college_id);
});

test('hod student access is limited to department programmes', function () {
    $this->seed(RolePermissionSeeder::class);

    $university = University::create(['name' => 'GTU']);
    $college = College::create([
        'university_id' => $university->university_id,
        'code' => 'LDRP2',
        'name' => 'LDRP Institute 2',
        'is_active' => true,
    ]);
    $ceDept = Department::create([
        'college_id' => $college->college_id,
        'code' => 'CE',
        'name' => 'Computer Engineering',
        'is_active' => true,
    ]);
    $meDept = Department::create([
        'college_id' => $college->college_id,
        'code' => 'ME',
        'name' => 'Mechanical Engineering',
        'is_active' => true,
    ]);
    $ceProgramme = Programme::create([
        'dept_id' => $ceDept->dept_id,
        'code' => 'BE_CE_TEST',
        'name' => 'BE Computer',
        'level' => 'UG',
        'is_active' => true,
    ]);
    $meProgramme = Programme::create([
        'dept_id' => $meDept->dept_id,
        'code' => 'BE_ME_TEST',
        'name' => 'BE Mechanical',
        'level' => 'UG',
        'is_active' => true,
    ]);
    $hod = userWithSeededRole('HOD', [
        'university_id' => $university->university_id,
        'college_id' => $college->college_id,
        'dept_id' => $ceDept->dept_id,
    ]);

    Student::create([
        'college_id' => $college->college_id,
        'programme_id' => $ceProgramme->programme_id,
        'enrollment_no' => 'CE_SCOPE_001',
        'first_name' => 'Visible',
        'last_name' => 'Student',
        'is_active' => true,
    ]);
    Student::create([
        'college_id' => $college->college_id,
        'programme_id' => $meProgramme->programme_id,
        'enrollment_no' => 'ME_SCOPE_001',
        'first_name' => 'Hidden',
        'last_name' => 'Student',
        'is_active' => true,
    ]);

    $this->actingAs($hod)
        ->get(route('students.index'))
        ->assertOk()
        ->assertSee('CE_SCOPE_001')
        ->assertDontSee('ME_SCOPE_001');
});
