<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'process-transaction',
            'manage-products',
            'manage-categories',
            'manage-stock-in',
            'manage-expenses',
            'manage-shifts',
            'manage-adjustments',
            'manage-settings',
            'manage-users',
            'view-financial-reports',
            'view-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Roles
        $ownerRole = Role::findOrCreate('owner', 'web');
        $adminRole = Role::findOrCreate('admin', 'web');
        $kasirRole = Role::findOrCreate('kasir', 'web');

        // Assign permissions
        $ownerRole->syncPermissions($permissions);
        $adminRole->syncPermissions([
            'process-transaction',
            'manage-products',
            'manage-categories',
            'manage-stock-in',
            'manage-expenses',
            'manage-shifts',
            'manage-adjustments',
            'view-financial-reports',
            'view-dashboard',
        ]);
        $kasirRole->syncPermissions([
            'process-transaction',
            'manage-shifts',
            'view-dashboard',
        ]);
    }
}
