<?php

use App\Models\College;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;

function superAdminForUniversity(University $university): User
{
    $role = UserRole::where('role_name', 'Super Admin')->firstOrFail();

    return User::factory()->create([
        'role_id' => $role->role_id,
        'university_id' => $university->university_id,
    ]);
}

test('colleges table is filtered by logged in user university id', function () {
    $this->seed(RolePermissionSeeder::class);

    $ownUniversity = University::create(['name' => 'Own College Scope University']);
    $otherUniversity = University::create(['name' => 'Other College Scope University']);
    $ownCollege = College::create([
        'university_id' => $ownUniversity->university_id,
        'code' => 'OWNCOL',
        'name' => 'Own Visible College',
        'is_active' => true,
    ]);
    College::create([
        'university_id' => $otherUniversity->university_id,
        'code' => 'OTHCOL',
        'name' => 'Other Hidden College',
        'is_active' => true,
    ]);

    $this->actingAs(superAdminForUniversity($ownUniversity))
        ->get(route('colleges.index'))
        ->assertOk()
        ->assertSee($ownCollege->name)
        ->assertDontSee('Other Hidden College');
});

test('college create forces the logged in user university id', function () {
    $this->seed(RolePermissionSeeder::class);

    $ownUniversity = University::create(['name' => 'Own Forced University']);
    $otherUniversity = University::create(['name' => 'Other Posted University']);

    $this->actingAs(superAdminForUniversity($ownUniversity))
        ->post(route('colleges.store'), [
            'university_id' => $otherUniversity->university_id,
            'code' => 'FORCED',
            'name' => 'Forced University College',
        ])
        ->assertRedirect(route('colleges.index'));

    $this->assertDatabaseHas('colleges', [
        'code' => 'FORCED',
        'name' => 'Forced University College',
        'university_id' => $ownUniversity->university_id,
    ]);
});

test('college edit outside logged in user university is forbidden', function () {
    $this->seed(RolePermissionSeeder::class);

    $ownUniversity = University::create(['name' => 'Own Edit Scope University']);
    $otherUniversity = University::create(['name' => 'Other Edit Scope University']);
    $otherCollege = College::create([
        'university_id' => $otherUniversity->university_id,
        'code' => 'NOEDIT',
        'name' => 'No Edit College',
        'is_active' => true,
    ]);

    $this->actingAs(superAdminForUniversity($ownUniversity))
        ->get(route('colleges.edit', $otherCollege))
        ->assertForbidden();
});
