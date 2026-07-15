<?php

use Illuminate\Support\Facades\Route;

// رابط الصفحة الرئيسية العادية للتطبيق
Route::get('/', function () {
    return view('welcome');
});
