<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewFeaturesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds for new features:
     * - Bulk Customer Recovery / Payments
     * - Distributor Reports
     * - Monthly Stock Closing
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Bulk Customer Payments / Recovery
            'bulk.customer.payments.view',
            'bulk.customer.payments.create',
            'bulk.customer.payments.edit',
            'bulk.customer.payments.delete',

            // Distributor Reports
            'distributor.reports.view',
            'distributor.reports.create',
            'distributor.reports.edit',
            'distributor.reports.delete',

            // Monthly Stock Closing
            'stock.closing.view',
            'stock.closing.create',
            'stock.closing.edit',
            'stock.closing.delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        // Assign all new permissions to Super Admin role
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $admin = Role::where('name', 'admin')->orWhere('name', 'Admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        // Reset cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
