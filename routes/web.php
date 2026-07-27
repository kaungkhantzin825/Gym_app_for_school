<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Convenient shortcuts to User Reports & Member Analytics in Admin Panel
Route::redirect('/user', '/admin/user-report-page');
Route::redirect('/users', '/admin/users');
Route::redirect('/report', '/admin/user-report-page');
Route::redirect('/reports', '/admin/user-report-page');
