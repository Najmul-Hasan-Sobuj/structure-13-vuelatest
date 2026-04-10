<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('guests cannot access users index', function () {
    $this->get(route('users.index'))
        ->assertRedirect(route('login'));
});

test('verified users can view users index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('users/Index')
            ->has('users.data')
            ->has('filters')
            ->has('can'));
});

test('users index filters by search', function () {
    User::factory()->create(['name' => 'Alpha Unique']);
    User::factory()->create(['name' => 'Beta Other']);
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('users.index', ['search' => 'Alpha Unique']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Alpha Unique'));
});

test('users index filters by verified state', function () {
    User::factory()->create(['email_verified_at' => now()]);
    User::factory()->unverified()->create();
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('users.index', ['verified' => 'yes']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('users.total', 2));

    $this->actingAs($actor)
        ->get(route('users.index', ['verified' => 'no']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('users.total', 1));
});

test('users index filters by created_at range', function () {
    User::factory()->create(['created_at' => now()->subDays(10)]);
    User::factory()->create(['created_at' => now()->subDay()]);
    $actor = User::factory()->create(['created_at' => now()->subDays(20)]);

    $from = now()->subDays(5)->toDateString();
    $to = now()->toDateString();

    $this->actingAs($actor)
        ->get(route('users.index', [
            'created_from' => $from,
            'created_to' => $to,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 1));
});

test('store validates required fields', function () {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->post(route('users.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

test('store creates a user', function () {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->post(route('users.store'), [
            'name' => 'New Person',
            'email' => 'new-person@example.com',
            'password' => 'password123!Aa',
            'password_confirmation' => 'password123!Aa',
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'new-person@example.com',
        'name' => 'New Person',
    ]);
});

test('update changes user attributes', function () {
    $actor = User::factory()->create();
    $target = User::factory()->create([
        'name' => 'Old',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($actor)
        ->patch(route('users.update', $target), [
            'name' => 'Updated',
            'email' => 'updated@example.com',
        ])
        ->assertRedirect();

    expect($target->fresh())
        ->name->toBe('Updated')
        ->email->toBe('updated@example.com');
});

test('destroy deletes another user', function () {
    $actor = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->delete(route('users.destroy', $target))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('users cannot delete themselves', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('users.destroy', $user))
        ->assertForbidden();
});
