<?php

use App\Models\College;
use App\Models\Department;
use App\Models\Permission;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;

function delegationUniversities(): array
{
    $ownUniversity = University::create(['name' => 'Delegation Own University']);
    $otherUniversity = University::create(['name' => 'Delegation Other University']);

    $ownCollege = College::create([
        'university_id' => $ownUniversity->university_id,
        'code' => 'DGOWN',
        'name' => 'Delegation Own College',
        'is_active' => true,
    ]);

    $otherCollege = College::create([
        'university_id' => $otherUniversity->university_id,
        'code' => 'DGOTH',
        'name' => 'Delegation Other College',
        'is_active' => true,
    ]);

    $ownDepartment = Department::create([
        'college_id' => $ownCollege->college_id,
        'code' => 'DGCE',
        'name' => 'Delegation Computer Department',
        'is_active' => true,
    ]);

    $otherDepartment = Department::create([
        'college_id' => $otherCollege->college_id,
        'code' => 'DGME',
        'name' => 'Delegation Mechanical Department',
        'is_active' => true,
    ]);

    return compact('ownUniversity', 'otherUniversity', 'ownCollege', 'otherCollege', 'ownDepartment', 'otherDepartment');
}

function delegationAdmin(array $attributes): User
{
    $role = UserRole::where('role_name', 'Admin')->firstOrFail();

    return User::factory()->create([
        'role_id' => $role->role_id,
        ...$attributes,
    ]);
}

test('college scoped creator is forced inside own college when creating users', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = delegationUniversities();

    $manager = delegationAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'university_id' => $fixtures['otherUniversity']->university_id,
            'username' => 'college.delegated',
            'email' => 'college.delegated@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
            'is_verified' => '1',
            'must_change_password' => '1',
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'college.delegated',
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
        'dept_id' => null,
        'programme_id' => null,
    ]);
});

test('college scoped creator cannot create user in another college department', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = delegationUniversities();

    $manager = delegationAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'dept_id' => $fixtures['otherDepartment']->dept_id,
            'username' => 'outside.department',
            'email' => 'outside.department@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
            'is_verified' => '1',
            'must_change_password' => '1',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('users', [
        'username' => 'outside.department',
    ]);
});

test('department scoped creator cannot assign admin role', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = delegationUniversities();

    $manager = delegationAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
        'dept_id' => $fixtures['ownDepartment']->dept_id,
    ]);

    $adminRole = UserRole::where('role_name', 'Admin')->firstOrFail();

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'role_id' => $adminRole->role_id,
            'username' => 'department.admin.blocked',
            'email' => 'department.admin.blocked@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
            'is_verified' => '1',
            'must_change_password' => '1',
        ])
        ->assertSessionHasErrors('role_id');

    $this->assertDatabaseMissing('users', [
        'username' => 'department.admin.blocked',
    ]);
});

test('university admin can assign admin role inside own university', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = delegationUniversities();

    $manager = delegationAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);

    $adminRole = UserRole::where('role_name', 'Admin')->firstOrFail();

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'role_id' => $adminRole->role_id,
            'username' => 'university.admin.equal',
            'email' => 'university.admin.equal@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
            'is_verified' => '1',
            'must_change_password' => '1',
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'university.admin.equal',
        'role_id' => $adminRole->role_id,
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => null,
    ]);
});

test('department scoped creator does not see higher roles in user form', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = delegationUniversities();

    $manager = delegationAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
        'dept_id' => $fixtures['ownDepartment']->dept_id,
    ]);

    $adminRole = UserRole::where('role_name', 'Admin')->firstOrFail();
    $superAdminRole = UserRole::where('role_name', 'Super Admin')->firstOrFail();

    $this->actingAs($manager)
        ->get(route('users.create'))
        ->assertOk()
        ->assertDontSeeHtml('<option value="'.$adminRole->role_id.'">Admin</option>')
        ->assertDontSeeHtml('<option value="'.$superAdminRole->role_id.'">Super Admin</option>');
});

test('custom role defaults are still limited to creator permissions', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = delegationUniversities();

    $manager = User::factory()->create([
        'role_id' => null,
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);

    $creatorPermissionIds = Permission::query()
        ->where(function ($query) {
            $query->where('module_name', 'user')->whereIn('action', ['view', 'create']);
        })
        ->orWhere(function ($query) {
            $query->where('module_name', 'dashboard')->where('action', 'view');
        })
        ->pluck('permission_id')
        ->all();
    $manager->permissions()->sync($creatorPermissionIds);

    $customRole = UserRole::create([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'created_by' => $manager->user_id,
        'role_name' => 'Delegated Operator',
        'is_active' => true,
    ]);

    $dashboard = Permission::where('module_name', 'dashboard')->where('action', 'view')->firstOrFail();
    $systemHealth = Permission::where('module_name', 'system_health')->where('action', 'view')->firstOrFail();
    $customRole->permissions()->sync([$dashboard->permission_id, $systemHealth->permission_id]);

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'role_id' => $customRole->role_id,
            'username' => 'delegated-custom-user',
            'email' => 'delegated-custom-user@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
            'is_verified' => '1',
            'must_change_password' => '1',
        ])
        ->assertRedirect(route('users.index'));

    $createdUser = User::where('username', 'delegated-custom-user')->firstOrFail();

    expect($createdUser->permissions()
        ->where('permissions.permission_id', $dashboard->permission_id)
        ->exists())->toBeTrue();

    expect($createdUser->permissions()
        ->where('permissions.permission_id', $systemHealth->permission_id)
        ->exists())->toBeFalse();
});
