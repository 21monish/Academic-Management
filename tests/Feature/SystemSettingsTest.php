<?php

use App\Models\Permission;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

function systemSettingsUser(): User
{
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );

    $user = User::factory()->create(['role_id' => $role->role_id]);

    $permissionIds = Permission::query()
        ->where('module_name', 'system_settings')
        ->whereIn('action', ['view', 'update'])
        ->pluck('permission_id')
        ->all();

    $user->permissions()->sync($permissionIds);

    return $user;
}

test('system settings can update application branding fields', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = systemSettingsUser();

    $this->actingAs($user)
        ->post(route('system.settings.update'), [
            '_method' => 'PUT',
            'application_name' => 'Campus One ERP',
            'application_short_name' => 'Campus Operations',
            'created_by' => 'Demo Creator',
            'created_by_contact' => '9999999999',
            'footer_text' => 'Created by Demo Creator',
            'support_email' => 'support@example.test',
            'support_phone' => '1234567890',
            'logo_url' => 'uploads/logos/demo-logo.png',
        ])
        ->assertRedirect(route('system.settings'));

    expect(SystemSetting::query()->where('setting_key', 'application_name')->value('setting_value'))->toBe('Campus One ERP');
    expect(SystemSetting::query()->where('setting_key', 'created_by')->value('setting_value'))->toBe('Demo Creator');

    $this->actingAs($user)
        ->get(route('system.settings'))
        ->assertOk()
        ->assertSee('Campus One ERP')
        ->assertSee('Created by Demo Creator');
});

test('system settings can upload application logo', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = systemSettingsUser();

    $this->actingAs($user)
        ->post(route('system.settings.update'), [
            '_method' => 'PUT',
            'application_name' => 'Logo ERP',
            'application_short_name' => 'Logo Operations',
            'created_by' => 'Logo Creator',
            'logo' => UploadedFile::fake()->image('system-logo.png', 120, 120),
        ])
        ->assertRedirect(route('system.settings'));

    $logoPath = SystemSetting::query()->where('setting_key', 'logo_url')->value('setting_value');

    expect($logoPath)->toStartWith('uploads/logos/');
    expect(File::exists(public_path($logoPath)))->toBeTrue();

    File::delete(public_path($logoPath));
});

test('system settings navigation follows direct page permission', function () {
    $this->seed(RolePermissionSeeder::class);

    $role = UserRole::query()->where('role_name', 'Admin')->firstOrFail();
    $user = User::factory()->create(['role_id' => $role->role_id]);
    $permission = Permission::query()
        ->where('module_name', 'system_settings')
        ->where('action', 'view')
        ->firstOrFail();

    $user->permissions()->sync([$permission->permission_id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('System Settings')
        ->assertDontSee('System Health');
});
