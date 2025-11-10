<?php

namespace App\Presentation\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionController
{
    public function index()
    {
        $guard = config('permission.default_guard', 'sanctum');
        return Permission::where('guard_name', $guard)->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $guard = config('permission.default_guard', 'sanctum');
        $perm = Permission::firstOrCreate(['name' => $data['name'], 'guard_name' => $guard]);
        return response()->json($perm, 201);
    }

    public function update(Request $request, string $permission)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $guard = config('permission.default_guard', 'sanctum');
        $model = Permission::where('name', $permission)->where('guard_name', $guard)->first();
        
        if (!$model) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        $exists = Permission::where('name', $data['name'])->where('guard_name', $guard)->where('id', '!=', $model->id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Permission name already exists'], 422);
        }

        $model->update(['name' => $data['name']]);

        return response()->json($model);
    }

    public function destroy(string $permission)
    {
        $guard = config('permission.default_guard', 'sanctum');
        $model = Permission::where('name', $permission)->where('guard_name', $guard)->first();
        if (!$model) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        DB::transaction(function () use ($model) {
            $permId = $model->id;
            DB::table('model_has_permissions')->where('permission_id', $permId)->delete();
            DB::table('role_has_permissions')->where('permission_id', $permId)->delete();
            DB::table('permissions')->where('id', $permId)->delete();
        });

        return response()->json(['ok' => true]);
    }
}
