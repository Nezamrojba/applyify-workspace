<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ModuleSettingSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'modules.universities.enabled' => true,
            'modules.courses.enabled' => true,
            'modules.countries.enabled' => true,
            'modules.applications.enabled' => true,
            'modules.users.enabled' => true,
            'modules.roles.enabled' => true,
            'modules.permissions.enabled' => true,
            'modules.notifications.enabled' => true,
            'modules.commission.enabled' => true,
            'modules.payments.enabled' => true,
            'modules.audits.enabled' => false,
            'modules.faqs.enabled' => true,
        ];

        foreach ($modules as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}

