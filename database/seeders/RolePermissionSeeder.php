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
        $permissions = [
            'view_house',
            'view_any_house',
            'create_house',
            'update_house',
            'delete_house',
            'delete_any_house',
            'view_resident',
            'view_any_resident',
            'create_resident',
            'update_resident',
            'delete_resident',
            'delete_any_resident',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign Permissions
        $roleSuperAdmin->syncPermissions($permissions);
        $roleAdminWarga->givePermissionTo(['view_house', 'view_any_house', 'view_resident', 'view_any_resident']); 
        
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
