<?php

use App\Http\Controllers\Academic\ProgrammeController;
use App\Http\Controllers\Academic\SemesterController;
use App\Http\Controllers\Academic\SubjectController;
use App\Http\Controllers\Academic\AcademicYearController;
use App\Http\Controllers\Academic\CategoryController;
use App\Http\Controllers\Academic\CurriculumController;
use App\Http\Controllers\Academic\ElectiveGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('academic')->name('academic.')->group(function () {
    Route::resource('categories', CategoryController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:category.view')
        ->middlewareFor(['create', 'store'], 'permission:category.create')
        ->middlewareFor(['edit', 'update'], 'permission:category.update')
        ->middlewareFor('destroy', 'permission:category.delete');

    Route::resource('academic-years', AcademicYearController::class)
        ->parameters(['academic-years' => 'academicYear'])
        ->except(['show'])
        ->middlewareFor('index', 'permission:academic_year.view')
        ->middlewareFor(['create', 'store'], 'permission:academic_year.create')
        ->middlewareFor(['edit', 'update'], 'permission:academic_year.update')
        ->middlewareFor('destroy', 'permission:academic_year.delete');

    Route::resource('programmes', ProgrammeController::class)
        ->middlewareFor(['index', 'show'], 'permission:programme.view')
        ->middlewareFor(['create', 'store'], 'permission:programme.create')
        ->middlewareFor(['edit', 'update'], 'permission:programme.update')
        ->middlewareFor('destroy', 'permission:programme.delete');

    // Activate/Deactivate (programmes table includes `is_active`)
    Route::patch('programmes/{programme}/activate', [ProgrammeController::class, 'activate'])
        ->middleware('permission:programme.update')
        ->name('programmes.activate');
    Route::patch('programmes/{programme}/deactivate', [ProgrammeController::class, 'deactivate'])
        ->middleware('permission:programme.update')
        ->name('programmes.deactivate');

    Route::resource('semesters', SemesterController::class)
        ->middlewareFor(['index', 'show'], 'permission:semester.view')
        ->middlewareFor(['create', 'store'], 'permission:semester.create')
        ->middlewareFor(['edit', 'update'], 'permission:semester.update')
        ->middlewareFor('destroy', 'permission:semester.delete');

    Route::patch(
        'semesters/{semester}/activate',
        [SemesterController::class, 'activate']
    )->middleware('permission:semester.update')->name('semesters.activate');

    Route::patch(
        'semesters/{semester}/deactivate',
        [SemesterController::class, 'deactivate']
    )->middleware('permission:semester.update')->name('semesters.deactivate');

    // Subjects
    Route::resource('subjects', SubjectController::class)
        ->middlewareFor(['index', 'show'], 'permission:subject.view')
        ->middlewareFor(['create', 'store'], 'permission:subject.create')
        ->middlewareFor(['edit', 'update'], 'permission:subject.update')
        ->middlewareFor('destroy', 'permission:subject.delete');
    Route::resource('curriculum', CurriculumController::class)
        ->parameters(['curriculum' => 'curriculum'])
        ->except(['show'])
        ->middlewareFor('index', 'permission:curriculum.view')
        ->middlewareFor(['create', 'store'], 'permission:curriculum.create')
        ->middlewareFor(['edit', 'update'], 'permission:curriculum.update')
        ->middlewareFor('destroy', 'permission:curriculum.delete');
    Route::resource('elective-groups', ElectiveGroupController::class)
        ->parameters(['elective-groups' => 'electiveGroup'])
        ->except(['show'])
        ->middlewareFor('index', 'permission:elective_group.view')
        ->middlewareFor(['create', 'store'], 'permission:elective_group.create')
        ->middlewareFor(['edit', 'update'], 'permission:elective_group.update')
        ->middlewareFor('destroy', 'permission:elective_group.delete');

    Route::patch('subjects/{subject}/activate', [SubjectController::class, 'activate'])
        ->middleware('permission:subject.update')
        ->name('subjects.activate');

    Route::patch('subjects/{subject}/deactivate', [SubjectController::class, 'deactivate'])
        ->middleware('permission:subject.update')
        ->name('subjects.deactivate');

});
