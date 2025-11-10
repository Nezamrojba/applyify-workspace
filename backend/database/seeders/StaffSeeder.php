<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffAccounts = [
            [
                'name' => 'Ali',
                'email' => 'ali@applyify.com',
                'password' => 'staff@use1',
                'whatsapp' => '+60123456789',
                'passport_no' => 'ALI001',
            ],
            [
                'name' => 'Ahmed',
                'email' => 'ahmed@applyify.com',
                'password' => 'staff@use2',
                'whatsapp' => '+60123456790',
                'passport_no' => 'AHM001',
            ],
            [
                'name' => 'Bariar',
                'email' => 'bariar@applyify.com',
                'password' => 'staff@use3',
                'whatsapp' => '+60123456791',
                'passport_no' => 'BAR001',
            ],
            [
                'name' => 'Osama',
                'email' => 'osama@applyify.com',
                'password' => 'staff@use4',
                'whatsapp' => '+60123456792',
                'passport_no' => 'OSA001',
            ],
        ];

        foreach ($staffAccounts as $staff) {
            $exists = DB::table('users')->where('email', $staff['email'])->exists();
            if (!$exists) {
                DB::table('users')->insert([
                    'name' => $staff['name'],
                    'email' => $staff['email'],
                    'password' => Hash::make($staff['password']),
                    'role' => 'staff',
                    'whatsapp' => $staff['whatsapp'],
                    'passport_no' => $staff['passport_no'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

