<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::where('code', '!=', 'MY')->delete();

        Country::updateOrCreate(
            ['code' => 'MY'],
            ['name' => 'Malaysia']
        );
    }
}
