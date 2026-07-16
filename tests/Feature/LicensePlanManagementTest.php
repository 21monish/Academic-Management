<?php

use App\Models\LicensePlan;
use App\Models\User;
use App\Models\UserRole;
use App\Models\University;
use Database\Seeders\RolePermissionSeeder;

function licensePlanManager(): User
{
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true, 'is_active' => true]
    );

    return User::factory()->create(['role_id' => $role->role_id]);
}

test('owner can manage subscription plans from system page', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = licensePlanManager();

    $this->actingAs($user)
        ->get(route('system.plans.index'))
        ->assertOk()
        ->assertSee('Manage Plans')
        ->assertSee('Create Plan');

    $this->actingAs($user)
        ->post(route('system.plans.store'), [
            'code' => 'starter',
            'name' => 'Starter',
            'monthly_price' => '1999',
            'max_students' => '500',
            'features' => ['core', 'institution', 'people'],
            'is_active' => '1',
        ])
        ->assertRedirect(route('system.plans.index'));

    $plan = LicensePlan::query()->where('code', 'starter')->firstOrFail();

    expect($plan->name)->toBe('Starter');
    expect((float) $plan->monthly_price)->toBe(1999.0);
    expect($plan->features)->toBe(['core', 'institution', 'people']);

    $this->actingAs($user)
        ->put(route('system.plans.update', $plan), [
            'code' => 'starter-plus',
            'name' => 'Starter Plus',
            'monthly_price' => '2499',
            'max_students' => '',
            'features' => ['*', 'fees'],
            'is_active' => '1',
        ])
        ->assertRedirect(route('system.plans.index'));

    $plan->refresh();

    expect($plan->code)->toBe('starter-plus');
    expect($plan->max_students)->toBeNull();
    expect($plan->features)->toBe(['*']);

    $this->actingAs($user)
        ->delete(route('system.plans.destroy', $plan))
        ->assertRedirect(route('system.plans.index'));

    expect(LicensePlan::query()->where('code', 'starter-plus')->exists())->toBeFalse();
});

test('assigned subscription plan cannot be deleted', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = licensePlanManager();
    $plan = LicensePlan::query()->create([
        'code' => 'client-plan',
        'name' => 'Client Plan',
        'monthly_price' => 4999,
        'features' => ['core'],
        'is_active' => true,
    ]);

    University::query()->create([
        'name' => 'Assigned University',
        'license_plan_id' => $plan->plan_id,
    ]);

    $this->actingAs($user)
        ->delete(route('system.plans.destroy', $plan))
        ->assertRedirect(route('system.plans.index'))
        ->assertSessionHas('error');

    expect(LicensePlan::query()->whereKey($plan->plan_id)->exists())->toBeTrue();
});
