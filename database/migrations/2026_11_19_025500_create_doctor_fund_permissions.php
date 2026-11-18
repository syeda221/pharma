<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $perms = [
            'doctor.funds.view',
            'doctor.funds.create',
            'doctor.funds.delete',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $superAdminRoles = Role::whereIn('name', ['Super Admin', 'superAdmin', 'admin', 'superadmin'])->get();
        foreach ($superAdminRoles as $role) {
            $role->givePermissionTo($perms);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $perms = [
            'doctor.funds.view',
            'doctor.funds.create',
            'doctor.funds.delete',
        ];

        Permission::whereIn('name', $perms)->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
