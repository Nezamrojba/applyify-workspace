<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController
{
    public function index()
    {
        return Country::query()->orderBy('name')->get();
    }

    public function show(Country $country)
    {
        return $country;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        
        return Country::create($data);
    }

    public function update(Request $request, Country $country)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        
        $country->update($data);
        return $country;
    }

    public function destroy(Country $country)
    {
        $country->delete();
        return response()->json(['ok' => true]);
    }
}
