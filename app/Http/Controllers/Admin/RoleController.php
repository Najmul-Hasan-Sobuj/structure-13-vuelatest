<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view roles', only: ['index']),
            new Middleware('permission:create roles', only: ['create', 'store']),
            new Middleware('permission:edit roles', only: ['edit', 'update']),
            new Middleware('permission:delete roles', only: ['destroy']),
        ];
    }

    public function index()
    {
        $permissions = Permission::where('guard_name', 'admin')->get()->groupBy('group_name');

        return Inertia::render('admin/roles/Index', [
            'roles' => Role::where('guard_name', 'admin')->with('permissions')->get(),
            'permissionGroups' => $permissions,
        ]);
    }

    public function create()
    {
        $permissions = Permission::where('guard_name', 'admin')->get()->groupBy('group_name');

        return Inertia::render('admin/roles/Create', [
            'permissionGroups' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'admin']);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'message' => 'Role created successfully.']);
    }

    public function edit(Role $role)
    {
        $permissions = Permission::where('guard_name', 'admin')->get()->groupBy('group_name');

        return Inertia::render('admin/roles/Edit', [
            'role' => $role->load('permissions'),
            'permissionGroups' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $validated['name']]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'message' => 'Role updated successfully.']);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'message' => 'Role deleted successfully.']);
    }
}
