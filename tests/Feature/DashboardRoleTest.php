<?php

use App\Models\User;
use App\Models\UserRole;
use App\Models\University;

test('single dashboard renders for users without a role by default', function () {
    $user = User::factory()->create(['role_id' => null]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('super admin role renders the single permission dashboard', function () {
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true, 'is_active' => true]
    );

    $university = University::query()->create(['name' => 'GTU']);

    $user = User::factory()->create([
        'role_id' => $role->role_id,
        'university_id' => $university->university_id,
        'username' => 'role-admin-user',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Owner Admin Dashboard')
        ->assertSee('Owner Command Center')
        ->assertSee('Monthly Income')
        ->assertSee('Dashboard')
        ->assertSee('direct permissions');
});
