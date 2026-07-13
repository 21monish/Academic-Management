<?php

use App\Http\Controllers\UserManagement\UserController;
use App\Http\Controllers\UserManagement\RoleController;
use App\Http\Controllers\System\SystemHealthController;
use App\Http\Controllers\System\SystemSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:user.view,user_permission.view,user_permission.update'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->middleware('permission:user.create')->name('create');
    Route::post('/', [UserController::class, 'store'])->middleware('permission:user.create')->name('store');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->middleware('permission:user.update')->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:user.update')->name('update');
    Route::get('/{user}/permissions', [UserController::class, 'editPermissions'])->middleware('permission:user_permission.view,user_permission.update')->name('permissions.edit');
    Route::patch('/{user}/permissions', [UserController::class, 'updatePermissions'])->middleware('permission:user_permission.update')->name('permissions.update');
    Route::patch('/{user}/activate', [UserController::class, 'activate'])->middleware('permission:user.update')->name('activate');
    Route::patch('/{user}/deactivate', [UserController::class, 'deactivate'])->middleware('permission:user.update')->name('deactivate');
    Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:user.delete')->name('destroy');
});

Route::middleware(['auth', 'permission:role.view'])->prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->middleware('permission:role.create')->name('create');
    Route::post('/', [RoleController::class, 'store'])->middleware('permission:role.create')->name('store');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:role.update')->name('edit');
    Route::put('/{role}', [RoleController::class, 'update'])->middleware('permission:role.update')->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('permission:role.delete')->name('destroy');
});

Route::get('/system/health', SystemHealthController::class)
    ->middleware(['auth', 'permission:system_health.view'])
    ->name('system.health');

Route::get('/system/settings', SystemSettingsController::class)
    ->middleware(['auth', 'permission:system_settings.view'])
    ->name('system.settings');

Route::put('/system/settings', [SystemSettingsController::class, 'update'])
    ->middleware(['auth', 'permission:system_settings.update'])
    ->name('system.settings.update');
