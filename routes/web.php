<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/reset-password/{token}', function ($token) {
    return view('auth.passwords.reset', ['token' => $token]);
})->name('password.reset');