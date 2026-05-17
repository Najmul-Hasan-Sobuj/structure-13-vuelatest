<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $groups = [
            'Roles' => [
                'view roles',
                'create roles',
                'edit roles',
                'delete roles',
            ],
            'Users' => [
                'view users',
                'create users',
                'edit users',
                'delete users',
            ],
            'Settings' => [
                'view settings',
                'edit settings',
            ],
        ];

        foreach ($groups as $groupName => $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate(
                    ['name' => $perm, 'guard_name' => 'admin'],
                    ['group_name' => $groupName]
                );
            }
        }

        // Refresh cache after creating permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create admin role and assign all admin permissions
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $role->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // Assign the role to seeded Default Admin if present
        $admin = Admin::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->assignRole($role);
        }
    }
}
