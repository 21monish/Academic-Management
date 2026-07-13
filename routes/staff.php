<?php

use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:staff.view'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->name('index');
    Route::get('/create', [StaffController::class, 'create'])->middleware('permission:staff.create')->name('create');
    Route::post('/', [StaffController::class, 'store'])->middleware('permission:staff.create')->name('store');

    Route::get('/{staff}/edit', [StaffController::class, 'edit'])->middleware('permission:staff.update')->name('edit');
    Route::put('/{staff}', [StaffController::class, 'update'])->middleware('permission:staff.update')->name('update');
    Route::patch('/{staff}', [StaffController::class, 'update'])->middleware('permission:staff.update')->name('update');

    Route::delete('/{staff}', [StaffController::class, 'destroy'])->middleware('permission:staff.delete')->name('destroy');
});
