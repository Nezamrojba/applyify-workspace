<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'sp@applyify.com';
        $password = 'admin@use1';
        $exists = DB::table('users')->where('email', $email)->exists();
        if (!$exists) {
            DB::table('users')->insert([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'whatsapp' => '+60000000000',
                'passport_no' => 'ADMIN-SEED',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

