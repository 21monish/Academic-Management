<?php

use App\Models\LicensePlan;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\LicensePlanSeeder;
use Database\Seeders\RolePermissionSeeder;

function licensedAdminForFeatureLock(string $planCode = 'basic', string $status = 'Active', ?string $expiresOn = null): User
{
    test()->seed(RolePermissionSeeder::class);
    test()->seed(LicensePlanSeeder::class);

    $plan = LicensePlan::query()->where('code', $planCode)->firstOrFail();
    $university = University::query()->create([
        'name' => 'Feature Lock University',
        'license_plan_id' => $plan->plan_id,
        'license_status' => $status,
        'license_expires_on' => $expiresOn,
    ]);
    $role = UserRole::query()->where('role_name', 'Admin')->firstOrFail();

    return User::factory()->create([
        'role_id' => $role->role_id,
        'university_id' => $university->university_id,
    ]);
}

test('basic plan allows certificates but locks fees', function () {
    $user = licensedAdminForFeatureLock('basic');

    $this->actingAs($user)
        ->get(route('reports.certificates'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('fees.categories'))
        ->assertForbidden();
});

test('premium plan allows fee modules', function () {
    $user = licensedAdminForFeatureLock('premium');

    $this->actingAs($user)
        ->get(route('fees.categories'))
        ->assertOk();
});

test('expired license blocks client access', function () {
    $user = licensedAdminForFeatureLock('premium', 'Active', now()->subDay()->format('Y-m-d'));

    $this->actingAs($user)
        ->get(route('reports.certificates'))
        ->assertForbidden();
});
