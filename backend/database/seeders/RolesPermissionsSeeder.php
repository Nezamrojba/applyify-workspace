<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect(['users.manage','roles.manage','permissions.manage'])
            ->map(fn($p) => Permission::firstOrCreate(['name' => $p, 'guard_name' => 'sanctum']));

        $super = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'sanctum']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'sanctum']);
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'sanctum']);

        $super->syncPermissions($permissions);

        $superAdminEmails = ['admin@example.com', 'sp@applyify.com'];
        $superAdmins = User::whereIn('email', $superAdminEmails)->get();
        foreach ($superAdmins as $admin) {
            $admin->syncRoles([$super]);
        }
    }
}

