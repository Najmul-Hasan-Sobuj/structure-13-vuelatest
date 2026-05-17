<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view users', only: ['index']),
            new Middleware('permission:create users', only: ['create', 'store']),
            new Middleware('permission:edit users', only: ['edit', 'update']),
            new Middleware('permission:delete users', only: ['destroy']),
        ];
    }

    public function index()
    {
        return Inertia::render('admin/users/Index', [
            'users' => Admin::with('roles')->get(),
            'roles' => Role::where('guard_name', 'admin')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/users/Create', [
            'roles' => Role::where('guard_name', 'admin')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email',
            'password' => ['required', Password::defaults()],
            'roles' => 'array',
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (! empty($validated['roles'])) {
            $admin->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.index')->with('toast', ['type' => 'success', 'message' => 'User created successfully.']);
    }

    public function edit(Admin $user)
    {
        return Inertia::render('admin/users/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::where('guard_name', 'admin')->get(),
        ]);
    }

    public function update(Request $request, Admin $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,'.$user->id,
            'password' => ['nullable', Password::defaults()],
            'roles' => 'array',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles($validated['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('toast', ['type' => 'success', 'message' => 'User updated successfully.']);
    }

    public function destroy(Admin $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('toast', ['type' => 'success', 'message' => 'User deleted successfully.']);
    }
}
