<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController
{
    public function index()
    {
        return Setting::query()->pluck('value', 'key');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        foreach ($data as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => is_array($v) ? $v : $v]);
        }
        return $this->index();
    }
}
