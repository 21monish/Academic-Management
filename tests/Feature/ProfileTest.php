<?php

use App\Models\User;
use App\Models\Permission;

function userWithProfilePermissions(array $actions = ['view', 'update', 'delete']): User
{
    $user = User::factory()->create();

    $permissionIds = collect($actions)->map(function (string $action) {
        return Permission::query()->updateOrCreate(
            ['module_name' => 'profile', 'action' => $action],
            ['description' => ucfirst($action).' profile']
        )->permission_id;
    })->all();

    $user->permissions()->syncWithoutDetaching($permissionIds);

    return $user;
}

test('profile page is displayed', function () {
    $user = userWithProfilePermissions(['view']);

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = userWithProfilePermissions(['view', 'update']);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = userWithProfilePermissions(['view', 'update']);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user cannot delete their own account', function () {
    $user = userWithProfilePermissions(['view', 'delete']);

    $response = $this
        ->from('/profile')
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertAuthenticated();
    $this->assertNotNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = userWithProfilePermissions(['view', 'delete']);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
