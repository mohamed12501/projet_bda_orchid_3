<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('platform.planning.runs');
    }
    return view('welcome');
});
