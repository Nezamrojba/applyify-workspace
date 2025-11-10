<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            StaffSeeder::class,
            RolesPermissionsSeeder::class,
            CountrySeeder::class,
            UniversityCourseSeeder::class,
            ModuleSettingSeeder::class,
        ]);
    }
}
