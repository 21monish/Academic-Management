<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = UserRole::updateOrCreate(
            [
                'role_name' => 'Super Admin',
                'university_id' => null,
            ],
            [
                'description' => 'Full system access',
                'is_system_role' => true,
                'is_active' => true,
            ]
        );

        $permissionIds = Permission::pluck('permission_id')->all();
        $superAdminRole->permissions()->sync($permissionIds);

        $admin = User::firstOrNew(['username' => 'admin']);
        $admin->fill([
            'role_id' => $superAdminRole->role_id,
            'university_id' => null,
            'college_id' => null,
            'dept_id' => null,
            'programme_id' => null,
            'email' => $admin->email ?: 'admin@gtu-erp.local',
            'is_active' => true,
            'is_verified' => true,
        ]);

        if (! $admin->exists || blank($admin->password_hash)) {
            $admin->password_hash = Hash::make('ChangeMe123!');
            $admin->must_change_password = true;
        }

        $admin->save();
        $admin->permissions()->sync($permissionIds);

        $this->command->info('Super admin is ready - username: admin / password: ChangeMe123! for fresh installs.');

        if ($admin->wasRecentlyCreated) {
            $this->command->warn('You will be forced to change this password on first login.');
        }
    }
}
