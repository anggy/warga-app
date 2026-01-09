<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $roleSuperAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        $roleAdminWarga = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin_warga']);
        $roleSatpam = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'satpam']);
        $roleWarga = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'warga']);

        // Default Permissions
        $resources = [
            'house',
            'resident',
            'ipl_payment',
            'vehicle',
            'inventory',
            'expense',
            'user',
            'role',
            'permission',
            'system_setting',
        ];

        $actions = [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'force_delete',
            'force_delete_any',
        ];

        $allPermissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $allPermissions[] = "{$action}_{$resource}";
            }
        }
        
        // Add specific page permissions if needed
        $allPermissions[] = 'page_ManageSystemSettings';

        foreach ($allPermissions as $permission) {
             \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign Permissions
        $roleSuperAdmin->syncPermissions($allPermissions);
        $roleAdminWarga->givePermissionTo($allPermissions); // Give full access to helper for now
        
        // Create Super Admin User
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@warga.app'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );

        $user->assignRole($roleSuperAdmin);
        
        // Create Sample Warga User
        $warga = \App\Models\User::firstOrCreate(
            ['email' => 'warga@warga.app'],
            [
                'name' => 'Contoh Warga',
                'password' => bcrypt('password'),
            ]
        );
        $warga->assignRole($roleWarga);
    }
}
