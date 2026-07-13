<?php

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('specific user permission manager can update permissions without account edit access', function () {
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
