<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('custom');
});

Route::get('/custom', function () {
    return view('custom');
});

Route::get('/schedule', function () {
    return view('schedule'); // Create schedule.blade.php if needed
})->name('schedule');

Route::get('/setup', function () {
    return view('setup'); // Create setup.blade.php if needed
})->name('setup');
