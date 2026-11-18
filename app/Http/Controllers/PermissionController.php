<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        foreach (['stock.adjust.view', 'stock.adjust.create', 'stock.adjust.edit', 'stock.adjust.delete', 'doctor.funds.view', 'doctor.funds.create', 'doctor.funds.delete'] as $permName) {
            Permission::firstOrCreate(['name' => $permName]);
        }
        $permissions = Permission::orderBy('name',"ASC")->get();
        return view('admin_panel.permissions.permission', compact('permissions'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        if ($currentUser && ! $currentUser->isSuperAdmin()) {
            return response()->json([
                'errors' => [
                    'name' => ['Only Super Admin can create or modify system permissions.'],
                ],
            ], 403);
        }

        $editId = $request->edit_id ?? null;
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name,' . $request->edit_id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Step 3: Save or update logic
        if (!empty($editId)) {
            $permission = Permission::findOrFail($editId);
            $msg = [
                'success' => 'Permission Updated Successfully',
                'reload' => true
            ];
        } else {
            $permission = new Permission();
            $msg = [
                'success' => 'Permission Created Successfully',
                'redirect' => route('permissions.index')
            ];
        }

        $permission->name = $request->name;
        $permission->save();

        return response()->json($msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $currentUser = auth()->user();
        if ($currentUser && ! $currentUser->isSuperAdmin()) {
            return redirect()->route('permissions.index')->with('error', 'Only Super Admin can delete system permissions.');
        }

        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
