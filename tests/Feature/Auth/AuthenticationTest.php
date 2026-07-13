<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'account_type' => 'administration',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can authenticate with remember me enabled', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'account_type' => 'administration',
        'email' => $user->email,
        'password' => 'password',
        'remember' => 'on',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    expect($user->fresh()->remember_token)->not->toBeNull();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'account_type' => 'administration',
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('staff users must select staff account type', function () {
    $user = User::factory()->create([
        'reference_type' => 'Staff',
        'reference_id' => 10,
    ]);

    $this->post('/login', [
        'account_type' => 'administration',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();

    $response = $this->post('/login', [
        'account_type' => 'staff',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('student users must select student account type', function () {
    $user = User::factory()->create([
        'reference_type' => 'Student',
        'reference_id' => 20,
    ]);

    $this->post('/login', [
        'account_type' => 'staff',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();

    $response = $this->post('/login', [
        'account_type' => 'student',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('stale sessions can logout without a database connection', function () {
    $defaultConnection = Config::get('database.default');
    Config::set('database.default', 'mysql');
    Config::set('database.connections.mysql.host', '127.0.0.1');
    Config::set('database.connections.mysql.port', 1);
    Config::set('database.connections.mysql.database', 'gtu_itr');

    $guardSessionKey = Auth::guard('web')->getName();

    $response = $this
        ->withSession([$guardSessionKey => 1])
        ->post('/logout');

    Config::set('database.default', $defaultConnection);

    $response->assertSessionMissing($guardSessionKey);
    $response->assertRedirect('/');
});
