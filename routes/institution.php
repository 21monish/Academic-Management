<?php

use App\Http\Controllers\CollegeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UniversityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Institution Module Routes (Module 1) — Super Admin only
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('universities', [UniversityController::class, 'index'])->middleware('permission:university.view')->name('universities.index');
    Route::get('universities/create', [UniversityController::class, 'create'])->middleware('permission:university.create')->name('universities.create');
    Route::post('universities', [UniversityController::class, 'store'])->middleware('permission:university.create')->name('universities.store');
    Route::get('universities/{university}/edit', [UniversityController::class, 'edit'])->middleware('permission:university.update')->name('universities.edit');
    Route::match(['put', 'patch'], 'universities/{university}', [UniversityController::class, 'update'])->middleware('permission:university.update')->name('universities.update');
    Route::delete('universities/{university}', [UniversityController::class, 'destroy'])->middleware('permission:university.delete')->name('universities.destroy');

    Route::get('colleges', [CollegeController::class, 'index'])->middleware('permission:college.view')->name('colleges.index');
    Route::get('colleges/create', [CollegeController::class, 'create'])->middleware('permission:college.create')->name('colleges.create');
    Route::post('colleges', [CollegeController::class, 'store'])->middleware('permission:college.create')->name('colleges.store');
    Route::get('colleges/{college}/edit', [CollegeController::class, 'edit'])->middleware('permission:college.update')->name('colleges.edit');
    Route::match(['put', 'patch'], 'colleges/{college}', [CollegeController::class, 'update'])->middleware('permission:college.update')->name('colleges.update');
    Route::delete('colleges/{college}', [CollegeController::class, 'destroy'])->middleware('permission:college.delete')->name('colleges.destroy');

    Route::get('departments', [DepartmentController::class, 'index'])->middleware('permission:department.view')->name('departments.index');
    Route::get('departments/create', [DepartmentController::class, 'create'])->middleware('permission:department.create')->name('departments.create');
    Route::post('departments', [DepartmentController::class, 'store'])->middleware('permission:department.create')->name('departments.store');
    Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->middleware('permission:department.update')->name('departments.edit');
    Route::match(['put', 'patch'], 'departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:department.update')->name('departments.update');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:department.delete')->name('departments.destroy');
});
