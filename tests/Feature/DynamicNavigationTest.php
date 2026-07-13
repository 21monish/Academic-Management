<?php

use App\Models\Permission;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;

function userWithRoleForNavigation(string $roleName): User
{
    $permissions = match ($roleName) {
        'Student' => [
            ['notice', 'view'],
        ],
        'Accountant' => [
            ['fees', 'view'],
            ['fees', 'create'],
            ['fees', 'update'],
            ['fees', 'delete'],
            ['fees', 'approve'],
            ['hall_ticket', 'view'],
            ['hall_ticket', 'approve'],
            ['notice', 'view'],
            ['reports', 'view'],
        ],
        'Limited Admin' => [
            ['college', 'view'],
            ['department', 'view'],
            ['user', 'view'],
            ['role', 'view'],
            ['student', 'view'],
            ['student_report', 'view'],
        ],
        default => [],
    };

    $role = $roleName === 'Super Admin'
        ? UserRole::where('role_name', 'Super Admin')->first()
        : null;

    $user = User::factory()->create(['role_id' => $role?->role_id]);

    if ($permissions) {
        $user->permissions()->sync(collect($permissions)->map(function (array $permission) {
            return Permission::where('module_name', $permission[0])
                ->where('action', $permission[1])
                ->value('permission_id');
        })->filter()->all());
    }

    return $user;
}

test('student navigation only shows permitted modules', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = userWithRoleForNavigation('Student');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Notices')
        ->assertDontSee('Students')
        ->assertDontSee('Attendance Summary')
        ->assertDontSee('Users')
        ->assertDontSee('Roles &amp; Permissions', false)
        ->assertDontSee('Fee Collection');
});

test('accountant navigation shows fees but hides academic management', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = userWithRoleForNavigation('Accountant');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Fee Collection')
        ->assertSee('Receipts')
        ->assertSee('Student Ledgers')
        ->assertDontSee('Subjects')
        ->assertDontSee('Staff Assignments');
});

test('super admin navigation still shows full administration', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = userWithRoleForNavigation('Super Admin');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Universities')
        ->assertSee('Roles &amp; Permissions', false)
        ->assertSee('System Settings')
        ->assertSee('System Health')
        ->assertSee('Fee Collection')
        ->assertSee('Practical Marks');
});

test('limited direct permissions show selected administration modules', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = userWithRoleForNavigation('Limited Admin');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Colleges')
        ->assertSee('Departments')
        ->assertSee('Users')
        ->assertSee('Roles &amp; Permissions', false)
        ->assertSee('Students')
        ->assertSee('Student Reports')
        ->assertDontSee('Universities')
        ->assertDontSee('System Settings')
        ->assertDontSee('System Health');
});

test('navigation uses logged in users university logo', function () {
    $this->seed(RolePermissionSeeder::class);

    $university = University::create([
        'name' => 'Logo Navigation University',
        'logo_url' => 'uploads/logos/navigation-logo.png',
    ]);
    $user = userWithRoleForNavigation('Limited Admin');
    $user->update(['university_id' => $university->university_id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Logo Navigation University')
        ->assertSee('uploads/logos/navigation-logo.png');
});
