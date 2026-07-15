<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_removed_demo_reset_automation_is_not_available(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create([
            'module_name' => 'system_settings',
            'action' => 'update',
            'description' => 'Update system settings',
        ]);

        $user->permissions()->attach($permission->permission_id);

        $this->actingAs($user)
            ->post(route('automations.run', 'demo-reset'))
            ->assertNotFound();
    }
}
