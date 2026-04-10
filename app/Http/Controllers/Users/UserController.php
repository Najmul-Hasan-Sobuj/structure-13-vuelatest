<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\IndexUserRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(IndexUserRequest $request): Response
    {
        $filters = $request->filters();

        $query = User::query()->forIndexFilters($filters);

        $query->orderBy($filters['sort'], $filters['direction']);

        $users = $query->paginate($filters['per_page'])->withQueryString();

        // `through()` mutates the paginator's items and returns the same instance; assign so the
        // transformed rows (including `can`) are obviously what we pass to Inertia.
        $users = $users->through(function (User $user) use ($request) {
            return array_merge(
                (new UserResource($user))->resolve(),
                [
                    'can' => [
                        'update' => $request->user()->can('update', $user),
                        'delete' => $request->user()->can('delete', $user),
                    ],
                ],
            );
        });

        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'verified' => $filters['verified'],
                'created_from' => $filters['created_from'],
                'created_to' => $filters['created_to'],
                'sort' => $filters['sort'],
                'direction' => $filters['direction'],
                'per_page' => $filters['per_page'],
            ],
            'can' => [
                'create' => $request->user()->can('create', User::class),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['password_confirmation']);
        User::query()->create($data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('User created.'),
        ]);

        return to_route('users.index');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        unset($data['password_confirmation']);

        if (($data['password'] ?? '') === '') {
            unset($data['password']);
        }

        $user->update($data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('User updated.'),
        ]);

        return to_route('users.index', $request->query());
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('User deleted.'),
        ]);

        return to_route('users.index', $request->query());
    }
}
