<?php

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('specific user permission manager can update permissions without account edit access', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create(['role_id' => null]);
    $studentReport = Permission::where('module_name', 'student_report')->where('action', 'view')->firstOrFail();
    $manager->permissions()->sync(Permission::query()
        ->where(function ($query) use ($studentReport) {
            $query->where('permissions.permission_id', $studentReport->permission_id)
                ->orWhere(function ($query) {
                    $query->where('module_name', 'user_permission')
                        ->whereIn('action', ['view', 'update']);
                });
        })
        ->pluck('permission_id')
        ->all());

    $target = User::factory()->create();

    $this->actingAs($manager)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Permissions')
        ->assertDontSee('Edit');

    $this->actingAs($manager)
        ->get(route('users.edit', $target))
        ->assertForbidden();

    $this->actingAs($manager)
        ->get(route('users.permissions.edit', $target))
        ->assertOk()
        ->assertSee('Update User Permissions')
        ->assertSee('User Permission Updater');

    $this->actingAs($manager)
        ->patch(route('users.permissions.update', $target), [
            'permissions' => [$studentReport->permission_id],
        ])
        ->assertRedirect(route('users.permissions.edit', $target));

    expect($target->fresh()->permissions()
        ->where('permissions.permission_id', $studentReport->permission_id)
        ->exists())->toBeTrue();
});

test('permission manager cannot assign permissions they do not own', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create(['role_id' => null]);
    $manager->permissions()->sync(Permission::query()
        ->where('module_name', 'user_permission')
        ->whereIn('action', ['view', 'update'])
        ->pluck('permission_id')
        ->all());

    $target = User::factory()->create();
    $studentReport = Permission::where('module_name', 'student_report')->where('action', 'view')->firstOrFail();

    $this->actingAs($manager)
        ->patch(route('users.permissions.update', $target), [
            'permissions' => [$studentReport->permission_id],
        ])
        ->assertSessionHasErrors('permissions');

    expect($target->fresh()->permissions()
        ->where('permissions.permission_id', $studentReport->permission_id)
        ->exists())->toBeFalse();
});

test('permission manager only removes permissions they own', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create(['role_id' => null]);
    $studentReport = Permission::where('module_name', 'student_report')->where('action', 'view')->firstOrFail();
    $systemHealth = Permission::where('module_name', 'system_health')->where('action', 'view')->firstOrFail();

    $manager->permissions()->sync(Permission::query()
        ->where(function ($query) use ($studentReport) {
            $query->where('permissions.permission_id', $studentReport->permission_id)
                ->orWhere(function ($query) {
                    $query->where('module_name', 'user_permission')
                        ->whereIn('action', ['view', 'update']);
                });
        })
        ->pluck('permission_id')
        ->all());

    $target = User::factory()->create(['role_id' => null]);
    $target->permissions()->sync([$studentReport->permission_id, $systemHealth->permission_id]);

    $this->actingAs($manager)
        ->patch(route('users.permissions.update', $target), [
            'permissions' => [],
        ])
        ->assertRedirect(route('users.permissions.edit', $target));

    $target->refresh();

    expect($target->permissions()
        ->where('permissions.permission_id', $studentReport->permission_id)
        ->exists())->toBeFalse();

    expect($target->permissions()
        ->where('permissions.permission_id', $systemHealth->permission_id)
        ->exists())->toBeTrue();
});
