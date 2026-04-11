<?php

namespace App\Services\Users;

use Illuminate\Http\Request;

/**
 * Builds a safe query array for redirects to users.index after update/destroy.
 * Form submissions may append spoofing or framework keys (e.g. _method); those must not be forwarded.
 */
final class UsersIndexRedirectQuery
{
    /**
     * Query keys aligned with the users index filters and pagination.
     *
     * @var list<string>
     */
    public const ALLOWED_KEYS = [
        'search',
        'verified',
        'created_from',
        'created_to',
        'sort',
        'direction',
        'per_page',
        'page',
    ];

    /**
     * @return array<string, mixed>
     */
    public function fromRequest(Request $request): array
    {
        return $request->only(self::ALLOWED_KEYS);
    }
}
