<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin login screen can be rendered', function (): void {
    $response = $this->get(route('admin.login'));

    $response->assertOk();
});

test('admins can authenticate using the login screen', function (): void {
    $admin = Admin::factory()->create();

    $response = $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin, 'admin');
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('admins can not authenticate with invalid password', function (): void {
    $admin = Admin::factory()->create();

    $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('admin');
});

test('admin dashboard requires authentication', function (): void {
    $response = $this->get(route('admin.dashboard', absolute: false));

    $response->assertRedirect(route('admin.login', absolute: false));
});

test('admins can logout', function (): void {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')->post(route('admin.logout'));

    $this->assertGuest('admin');
    $response->assertRedirect(route('admin.login', absolute: false));
});

test('admins are rate limited', function (): void {
    $admin = Admin::factory()->create();

    foreach (range(1, 5) as $_) {
        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});
