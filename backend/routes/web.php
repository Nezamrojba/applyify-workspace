<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/__noop', function () {
    return response()->json(['ok' => true]);
});
