<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('queue monitor page is displayed for authenticated verified users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('queue-monitor.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Queue')
            ->has('connectionName')
            ->has('driver')
            ->has('pendingSize')
            ->has('failedCount')
            ->has('reservedCount')
            ->has('batchSummary'),
        );
});

test('guests are redirected from queue monitor page', function () {
    $this->get(route('queue-monitor.edit'))
        ->assertRedirect(route('login'));
});
