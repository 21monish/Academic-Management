<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $university = University::firstOrCreate(
            ['name' => 'Gujarat Technological University'],
            [
                'address' => 'Chandkheda, Ahmedabad, Gujarat',
                'email' => 'info@gtu.ac.in',
                'website' => 'https://www.gtu.ac.in',
                'established_date' => '2007-05-15',
            ]
        );

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
            'university_id' => $university->university_id,
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
