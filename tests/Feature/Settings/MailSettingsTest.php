<?php

use App\Models\MailSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('mail settings page is displayed for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('mail-settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/MailSettings')
            ->has('settings')
            ->where('passwordIsSet', false)
            ->missing('settings.password'),
        );
});

test('guests are redirected from mail settings', function () {
    $this->get(route('mail-settings.edit'))
        ->assertRedirect(route('login'));
});

test('mail settings can be saved', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('mail-settings.update'), [
            'mailer' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'user',
            'password' => 'secret-smtp',
            'from_address' => 'from@example.com',
            'from_name' => 'Example',
        ])
        ->assertRedirect(route('mail-settings.edit'));

    $row = MailSetting::query()->first();
    expect($row)->not->toBeNull()
        ->and($row->host)->toBe('smtp.example.com')
        ->and($row->port)->toBe(587)
        ->and($row->encryption)->toBe('tls')
        ->and($row->username)->toBe('user')
        ->and($row->from_address)->toBe('from@example.com')
        ->and($row->from_name)->toBe('Example');

    expect($row->password)->toBe('secret-smtp');
});

test('mail settings can be cleared when host is empty', function () {
    $user = User::factory()->create();

    MailSetting::query()->create([
        'mailer' => 'smtp',
        'host' => 'smtp.example.com',
        'port' => 587,
        'encryption' => null,
        'username' => null,
        'password' => 'x',
        'from_address' => 'from@example.com',
        'from_name' => 'App',
    ]);

    $this->actingAs($user)
        ->patch(route('mail-settings.update'), [
            'mailer' => 'smtp',
            'host' => '',
            'port' => null,
            'encryption' => null,
            'username' => null,
            'password' => null,
            'from_address' => 'from@example.com',
            'from_name' => 'App',
        ])
        ->assertRedirect(route('mail-settings.edit'));

    expect(MailSetting::query()->count())->toBe(0);
});

test('test email endpoint responds successfully', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('mail-settings.test'))
        ->assertRedirect();
});
