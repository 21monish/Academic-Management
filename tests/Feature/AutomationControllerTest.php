<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_reset_runs_demo_seeders(): void
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
            ->assertRedirect()
            ->assertSessionHas('status', 'demo reset: demo cleanup + seed complete');
    }
}
