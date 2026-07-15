<?php

use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Automation\AutomationController;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/features', 'public.features')->name('public.features');
Route::view('/modules', 'public.modules')->name('public.modules');
Route::view('/about', 'public.about')->name('public.about');
Route::view('/contact', 'public.contact')->name('public.contact');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/institution.php';
require __DIR__.'/academic.php';

require __DIR__.'/staff.php';
require __DIR__.'/student.php';
require __DIR__.'/attendance.php';
require __DIR__.'/exam.php';
require __DIR__.'/fees.php';
require __DIR__.'/leave.php';
require __DIR__.'/notice.php';
require __DIR__.'/reports.php';
require __DIR__.'/users.php';
require __DIR__.'/chatbot.php';

Route::middleware(['auth'])->group(function () {
    Route::post('/automations/{task}', [AutomationController::class, 'run'])->name('automations.run');

    Route::get('/profile', [ProfileController::class, 'edit'])->middleware('permission:profile.view')->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('permission:profile.update')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('permission:profile.delete')->name('profile.destroy');

    Route::get('/change-password', [PasswordChangeController::class, 'show'])->middleware('permission:password_change.view')->name('password.change.show');
    Route::put('/change-password', [PasswordChangeController::class, 'update'])->middleware('permission:password_change.update')->name('password.change.update');
});
