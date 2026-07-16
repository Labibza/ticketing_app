<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home');

Route::get(
    '/events/{event}',
    [EventController::class, 'show']
)->name('events.show');

/*
|--------------------------------------------------------------------------
| Dashboard Admin
|--------------------------------------------------------------------------
*/

Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)
    ->middleware([
        'auth',
        'verified',
        'admin',
    ])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Category Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware([
        'auth',
        'verified',
        'admin',
    ])
    ->name('categories.')
    ->group(function () {
        Route::get(
            '/categories',
            [DashboardController::class, 'index']
        )->name('index');

        Route::post(
            '/categories',
            [CategoryController::class, 'store']
        )->name('store');

        Route::put(
            '/categories/{id}',
            [CategoryController::class, 'update']
        )->name('update');

        Route::delete(
            '/categories/{id}',
            [CategoryController::class, 'destroy']
        )->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Event Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.events.')
    ->middleware([
        'auth',
        'verified',
        'admin',
    ])
    ->group(function () {
        Route::get(
            '/events',
            [EventController::class, 'index']
        )->name('index');

        Route::get(
            '/events/create',
            [EventController::class, 'create']
        )->name('create');

        Route::post(
            '/events',
            [EventController::class, 'store']
        )->name('store');

        Route::get(
            '/events/{event}/edit',
            [EventController::class, 'edit']
        )->name('edit');

        Route::put(
            '/events/{event}',
            [EventController::class, 'update']
        )->name('update');

        Route::delete(
            '/events/{event}',
            [EventController::class, 'destroy']
        )->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

require __DIR__ . '/auth.php';