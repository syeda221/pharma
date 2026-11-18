<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        // Ensure Doctor Fund permissions exist in database
        foreach (['doctor.funds.view', 'doctor.funds.create', 'doctor.funds.delete'] as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        $superAdminRoles = Role::whereIn('name', ['Super Admin', 'superAdmin', 'admin', 'superadmin'])->get();
        foreach ($superAdminRoles as $sar) {
            $sar->givePermissionTo(['doctor.funds.view', 'doctor.funds.create', 'doctor.funds.delete']);
        }

        $currentUser = auth()->user();
        if ($currentUser) {
            $currentUser->givePermissionTo(['doctor.funds.view', 'doctor.funds.create', 'doctor.funds.delete']);
        }

        if ($currentUser && $currentUser->isSuperAdmin()) {
            $roles = Role::orderBy('name', "ASC")->get();
            $allPermissions = Permission::orderBy('name', "ASC")->get();
        } else {
            $roles = $currentUser ? $currentUser->getManageableRoles() : collect();
            $allPermissions = $currentUser ? $currentUser->getManageablePermissions() : collect();
        }

        return view('admin_panel.roles.role', compact(['roles', 'allPermissions']));
    }

    public function store(Request $request)
    {
        $editId = $request->edit_id ?? null;
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,'.$request->edit_id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Save or update logic
        if (!empty($editId)) {
            $role = Role::findOrFail($editId);
            $currentUser = auth()->user();

            // Prevent renaming default system roles
            if (in_array(strtolower($role->name), ['super admin', 'superadmin', 'admin']) && strtolower($role->name) !== strtolower($request->name)) {
                return response()->json([
                    'errors' => [
                        'name' => ['System default role names cannot be modified.']
                    ]
                ], 422);
            }

            $msg = [
                'success' => 'Role Updated Successfully',
                'reload' => true
            ];
        } else {
            $role = new Role();
            $msg = [
                'success' => 'Role Created Successfully',
                'redirect' => route('roles.index')
            ];
        }

        $role->name = $request->name;
        $role->save();

        return response()->json($msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $currentUser = auth()->user();

        // Protect Super Admin / Admin roles from deletion
        if (in_array(strtolower($role->name), ['super admin', 'superadmin', 'admin'])) {
            return redirect()->route('roles.index')->with('error', 'Default system roles cannot be deleted.');
        }

        if ($currentUser && ! $currentUser->isSuperAdmin()) {
            $myPermNames = $currentUser->getAllPermissions()->pluck('name')->toArray();
            $rolePermNames = $role->permissions->pluck('name')->toArray();
            
            // If the role has permissions that the current user does not have, prevent deletion
            if (! empty(array_diff($rolePermNames, $myPermNames))) {
                return redirect()->route('roles.index')->with('error', 'You cannot delete roles containing permissions you do not possess.');
            }
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    public function updatePermissions(Request $request)
    {
        $request->validate([
            'edit_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::with('permissions')->findOrFail($request->edit_id);
        $currentUser = auth()->user();

        if ($currentUser && $currentUser->isSuperAdmin()) {
            // Super Admin can sync all permissions freely
            $role->syncPermissions($request->permissions ?? []);
        } else {
            // Delegated user can only grant or revoke permissions they themselves possess
            $userPermNames = $currentUser ? $currentUser->getAllPermissions()->pluck('name')->toArray() : [];
            $currentRolePermNames = $role->permissions->pluck('name')->toArray();

            // Permissions submitted that the acting user owns
            $submittedPerms = array_values(array_intersect($request->permissions ?? [], $userPermNames));

            // Existing permissions on the role that the acting user does NOT own (preserve them)
            $unmanagedPerms = array_values(array_diff($currentRolePermNames, $userPermNames));

            // Merge unmanaged permissions with user's granted permissions
            $finalPerms = array_values(array_unique(array_merge($unmanagedPerms, $submittedPerms)));

            $role->syncPermissions($finalPerms);
        }

        return back()->with('success', 'Role permissions updated successfully!');
    }
}
