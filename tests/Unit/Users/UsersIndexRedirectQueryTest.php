<?php

use App\Services\Users\UsersIndexRedirectQuery;
use Illuminate\Http\Request;

test('from request keeps only allowlisted index query keys', function () {
    $request = Request::create('/users', 'GET', [
        '_method' => 'PATCH',
        'search' => 'foo',
        'sort' => 'name',
        'evil' => 'bar',
    ]);

    $query = app(UsersIndexRedirectQuery::class)->fromRequest($request);

    expect($query)
        ->toHaveKey('search', 'foo')
        ->toHaveKey('sort', 'name')
        ->not->toHaveKey('_method')
        ->not->toHaveKey('evil');
});
