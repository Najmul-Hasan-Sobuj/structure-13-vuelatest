<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class AuthenticateAdmin extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return route('admin.login', absolute: false);
    }
}
