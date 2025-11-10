<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController
{
    public function assign(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', 'in:super_admin,staff']]);
        $roleName = $data['role'];
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'sanctum']);
        $user->assignRole($role);
        $user->update(['role' => $roleName]);
        return response()->json(['ok' => true]);
    }

    public function revoke(User $user, string $role)
    {
        $user->removeRole($role);
        return response()->json(['ok' => true]);
    }

    public function show(User $user)
    {
        return [
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getPermissionNames(),
        ];
    }
}
