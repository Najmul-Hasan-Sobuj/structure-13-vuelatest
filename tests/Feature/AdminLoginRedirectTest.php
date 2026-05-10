<?php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dashboard redirects unauthenticated', function () {
    $response = $this->get('/admin');
    $response->dump();
    $response->assertStatus(302);
});

