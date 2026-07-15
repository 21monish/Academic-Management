<?php

use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentElectiveChoiceController;
use App\Http\Controllers\Student\StudentEnrollmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:student.view'])->prefix('students')->name('students.')->group(function () {
    Route::get('/', [StudentController::class, 'index'])->name('index');
    Route::get('/create', [StudentController::class, 'create'])->middleware('permission:student.create')->name('create');
    Route::post('/', [StudentController::class, 'store'])->middleware('permission:student.create')->name('store');

    Route::post('/{student}/enrollments', [StudentEnrollmentController::class, 'store'])->middleware('permission:student.update')->name('enrollments.store');
    Route::patch('/{student}/enrollments/{enrollment}', [StudentEnrollmentController::class, 'update'])->middleware('permission:student.update')->name('enrollments.update');
    Route::delete('/{student}/enrollments/{enrollment}', [StudentEnrollmentController::class, 'destroy'])->middleware('permission:student.update')->name('enrollments.destroy');
    Route::post('/{student}/enrollments/{enrollment}/electives', [StudentElectiveChoiceController::class, 'store'])->middleware('permission:student.update')->name('electives.store');
    Route::delete('/{student}/enrollments/{enrollment}/electives/{choice}', [StudentElectiveChoiceController::class, 'destroy'])->middleware('permission:student.update')->name('electives.destroy');

    Route::get('/{student}', [StudentController::class, 'show'])->name('show');
    Route::get('/{student}/edit', [StudentController::class, 'edit'])->middleware('permission:student.update')->name('edit');
    Route::match(['put', 'patch'], '/{student}', [StudentController::class, 'update'])->middleware('permission:student.update')->name('update');

    Route::delete('/{student}', [StudentController::class, 'destroy'])->middleware('permission:student.delete')->name('destroy');

    Route::patch('/{student}/activate', [StudentController::class, 'activate'])->middleware('permission:student.update')->name('activate');
    Route::patch('/{student}/deactivate', [StudentController::class, 'deactivate'])->middleware('permission:student.update')->name('deactivate');
});
