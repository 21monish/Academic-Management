<?php

use App\Models\ApprovalRequest;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

function approvalPermissionUser(array $permissionSlugs): User
{
    $user = User::factory()->create(['role_id' => null]);
    $permissionIds = collect($permissionSlugs)->map(function (string $slug) {
        [$module, $action] = explode('.', $slug, 2);

        return Permission::where('module_name', $module)
            ->where('action', $action)
            ->value('permission_id');
    })->filter()->all();

    $user->permissions()->sync($permissionIds);

    return $user;
}

test('approval requests page requires approval request view permission', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = approvalPermissionUser([]);

    $this->actingAs($user)
        ->get(route('approvals.index'))
        ->assertForbidden();

    $user->permissions()->sync(Permission::where('module_name', 'approval_request')
        ->where('action', 'view')
        ->pluck('permission_id')
        ->all());

    ApprovalRequest::create([
        'action' => 'delete_user',
        'subject_type' => User::class,
        'subject_id' => $user->user_id,
        'requested_by' => $user->user_id,
        'payload' => ['username' => $user->username],
        'status' => ApprovalRequest::STATUS_PENDING,
        'requested_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('approvals.index'))
        ->assertOk()
        ->assertSee('Approval Requests')
        ->assertSee('Delete User');
});

test('approval request view permission does not allow approve or reject actions', function () {
    $this->seed(RolePermissionSeeder::class);

    $requester = approvalPermissionUser([]);
    $approver = approvalPermissionUser(['approval_request.view']);
    $approval = ApprovalRequest::create([
        'action' => 'delete_user',
        'subject_type' => User::class,
        'subject_id' => $requester->user_id,
        'requested_by' => $requester->user_id,
        'payload' => ['username' => $requester->username],
        'status' => ApprovalRequest::STATUS_PENDING,
        'requested_at' => now(),
    ]);

    $this->actingAs($approver)
        ->patch(route('approvals.approve', $approval))
        ->assertForbidden();

    $this->actingAs($approver)
        ->patch(route('approvals.reject', $approval))
        ->assertForbidden();
});
