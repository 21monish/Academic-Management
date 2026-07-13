<?php

use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;

function roleScopeAdmin(University $university): User
{
    $role = UserRole::where('role_name', 'Super Admin')->firstOrFail();

    return User::factory()->create([
        'role_id' => $role->role_id,
        'university_id' => $university->university_id,
    ]);
}

test('roles list is filtered by logged in user university id', function () {
    $this->seed(RolePermissionSeeder::class);

    $ownUniversity = University::create(['name' => 'Role Scope University']);
    $otherUniversity = University::create(['name' => 'Other Role Scope University']);

    UserRole::create([
        'university_id' => $ownUniversity->university_id,
        'role_name' => 'Own University Clerk',
        'description' => 'Visible role',
        'is_active' => true,
    ]);
    UserRole::create([
        'university_id' => $otherUniversity->university_id,
        'role_name' => 'Other University Clerk',
        'description' => 'Hidden role',
        'is_active' => true,
    ]);

    $this->actingAs(roleScopeAdmin($ownUniversity))
        ->get(route('roles.index'))
        ->assertOk()
        ->assertSee('Super Admin')
        ->assertSee('Own University Clerk')
        ->assertDontSee('Other University Clerk');
});

test('user role dropdown is filtered by logged in user university id', function () {
    $this->seed(RolePermissionSeeder::class);

    $ownUniversity = University::create(['name' => 'User Role Scope University']);
    $otherUniversity = University::create(['name' => 'Other User Role Scope University']);

    UserRole::create([
        'university_id' => $ownUniversity->university_id,
        'role_name' => 'Own Form Role',
        'is_active' => true,
    ]);
    UserRole::create([
        'university_id' => $otherUniversity->university_id,
        'role_name' => 'Other Form Role',
        'is_active' => true,
    ]);

    $this->actingAs(roleScopeAdmin($ownUniversity))
        ->get(route('users.create'))
        ->assertOk()
        ->assertSee('Own Form Role')
        ->assertDontSee('Other Form Role');
});

test('profile managed staff and student roles cannot be manually selected for users', function () {
    $this->seed(RolePermissionSeeder::class);

    $university = University::create(['name' => 'Profile Managed Role University']);
    $manager = roleScopeAdmin($university);
    $staffRole = UserRole::create([
        'university_id' => $university->university_id,
        'role_name' => 'Manual User Faculty Role',
        'description' => 'Managed from staff profile',
        'staff_type' => 'Teaching',
        'is_active' => true,
    ]);

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'role_id' => $staffRole->role_id,
            'username' => 'manual.staff',
            'email' => 'manual.staff@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'is_verified' => true,
            'must_change_password' => true,
        ])
        ->assertSessionHasErrors('role_id');

    $this->assertDatabaseMissing('users', [
        'username' => 'manual.staff',
    ]);
});

test('new role is assigned to logged in user university', function () {
    $this->seed(RolePermissionSeeder::class);

    $ownUniversity = University::create(['name' => 'Role Create Scope University']);
    $otherUniversity = University::create(['name' => 'Other Role Create Scope University']);

    $this->actingAs(roleScopeAdmin($ownUniversity))
        ->post(route('roles.store'), [
            'university_id' => $otherUniversity->university_id,
            'role_name' => 'Scoped Created Role',
            'description' => 'Created from scoped user',
            'is_active' => true,
        ])
        ->assertRedirect(route('roles.index'));

    $this->assertDatabaseHas('user_roles', [
        'role_name' => 'Scoped Created Role',
        'university_id' => $ownUniversity->university_id,
    ]);
});

test('role can store staff type classification', function () {
    $this->seed(RolePermissionSeeder::class);

    $university = University::create(['name' => 'Role Staff Type University']);
    $manager = roleScopeAdmin($university);

    $this->actingAs($manager)
        ->post(route('roles.store'), [
            'role_name' => 'Hybrid Staff Role',
            'description' => 'Can be used for both staff types',
            'staff_type' => 'Both',
            'is_active' => true,
        ])
        ->assertRedirect(route('roles.index'));

    $this->assertDatabaseHas('user_roles', [
        'role_name' => 'Hybrid Staff Role',
        'university_id' => $university->university_id,
        'staff_type' => 'Both',
    ]);

    $role = UserRole::query()->where('role_name', 'Hybrid Staff Role')->firstOrFail();

    $this->actingAs($manager)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertSee('Hybrid Staff Role')
        ->assertSee('Both');

    $this->actingAs($manager)
        ->put(route('roles.update', $role), [
            'role_name' => 'Hybrid Staff Role',
            'description' => 'Teaching only now',
            'staff_type' => 'Teaching',
            'is_active' => true,
        ])
        ->assertRedirect(route('roles.index'));

    $this->assertDatabaseHas('user_roles', [
        'role_id' => $role->role_id,
        'staff_type' => 'Teaching',
    ]);
});
