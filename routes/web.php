<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResourceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
|
| Laravel + Inertia + Vue. `auth` decides whether somebody is signed in,
| `admin` decides whether they may be here at all.
|
*/
Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('calendar', CalendarController::class)->name('calendar');
        Route::get('report', ReportController::class)->name('report');

        // One route pair per screen; the resource key selects the definition.
        Route::get('{resource}', [ResourceController::class, 'index'])->name('resource.index');
        Route::get('{resource}/create', [ResourceController::class, 'create'])->name('resource.create');
        Route::post('{resource}', [ResourceController::class, 'store'])->name('resource.store');
        Route::get('{resource}/{id}/edit', [ResourceController::class, 'edit'])->name('resource.edit');
        Route::put('{resource}/{id}', [ResourceController::class, 'update'])->name('resource.update');
        Route::delete('{resource}/{id}', [ResourceController::class, 'destroy'])->name('resource.destroy');
    });
});

// Shortcuts people may already have bookmarked.
Route::redirect('/users', '/admin/users');
Route::redirect('/user', '/admin/report');
Route::redirect('/report', '/admin/report');
Route::redirect('/reports', '/admin/report');
