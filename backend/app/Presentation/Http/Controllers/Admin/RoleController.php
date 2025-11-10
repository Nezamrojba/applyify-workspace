<?php

namespace App\Presentation\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController
{
    public function index()
    {
        $guard = config('permission.default_guard', 'sanctum');
        return Role::where('guard_name', $guard)->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $guard = config('permission.default_guard', 'sanctum');
        $role = Role::firstOrCreate(['name' => $data['name'], 'guard_name' => $guard]);
        return response()->json($role, 201);
    }

    public function update(Request $request, string $role)
    {
        if ($role === 'super_admin') {
            return response()->json(['message' => 'Cannot rename super_admin role'], 422);
        }

        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $guard = config('permission.default_guard', 'sanctum');
        $model = Role::where('name', $role)->where('guard_name', $guard)->first();
        
        if (!$model) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $exists = Role::where('name', $data['name'])->where('guard_name', $guard)->where('id', '!=', $model->id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Role name already exists'], 422);
        }

        $model->update(['name' => $data['name']]);

        return response()->json($model);
    }

    public function syncPermissions(Request $request, string $role)
    {
        $data = $request->validate(['permissions' => ['array'], 'permissions.*' => ['string']]);
        $guard = config('permission.default_guard', 'sanctum');
        $roleModel = Role::where('name', $role)->where('guard_name', $guard)->first();
        if (!$roleModel) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        $perms = collect($data['permissions'] ?? [])->map(function ($name) use ($guard) {
            return Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        });
        $roleModel->syncPermissions($perms);
        return response()->json(['ok' => true]);
    }

    public function destroy(string $role)
    {
        if ($role === 'super_admin') {
            return response()->json(['message' => 'Cannot delete super_admin role'], 422);
        }
        $guard = config('permission.default_guard', 'sanctum');
        $model = Role::where('name', $role)->where('guard_name', $guard)->first();
        if (!$model) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        DB::transaction(function () use ($model) {
            $roleId = $model->id;
            DB::table('model_has_roles')->where('role_id', $roleId)->delete();
            DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        });

        return response()->json(['ok' => true]);
    }
}
