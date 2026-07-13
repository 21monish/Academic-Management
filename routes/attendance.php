<?php

use App\Http\Controllers\Attendance\AttendanceModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('assignments', [AttendanceModuleController::class, 'assignments'])->middleware('permission:staff_assignment.view')->name('assignments');
    Route::post('assignments', [AttendanceModuleController::class, 'storeAssignment'])->middleware('permission:staff_assignment.create,staff_assignment.update')->name('assignments.store');
    Route::delete('assignments/{assignment}', [AttendanceModuleController::class, 'destroyAssignment'])->middleware('permission:staff_assignment.delete,staff_assignment.update')->name('assignments.destroy');

    Route::get('slots', [AttendanceModuleController::class, 'slots'])->middleware('permission:timetable_slot.view')->name('slots');
    Route::post('slots', [AttendanceModuleController::class, 'storeSlot'])->middleware('permission:timetable_slot.create,timetable_slot.update')->name('slots.store');
    Route::delete('slots/{slot}', [AttendanceModuleController::class, 'destroySlot'])->middleware('permission:timetable_slot.delete,timetable_slot.update')->name('slots.destroy');

    Route::get('lectures', [AttendanceModuleController::class, 'lectures'])->middleware('permission:lecture.view')->name('lectures');
    Route::post('lectures', [AttendanceModuleController::class, 'storeLecture'])->middleware('permission:lecture.create,lecture.update')->name('lectures.store');
    Route::delete('lectures/{lecture}', [AttendanceModuleController::class, 'destroyLecture'])->middleware('permission:lecture.delete,lecture.update')->name('lectures.destroy');

    Route::get('lectures/{lecture}/mark', [AttendanceModuleController::class, 'mark'])->middleware('permission:lecture.create,lecture.update')->name('mark');
    Route::post('lectures/{lecture}/mark', [AttendanceModuleController::class, 'storeMark'])->middleware('permission:lecture.create,lecture.update')->name('mark.store');

    Route::get('summaries', [AttendanceModuleController::class, 'summaries'])->middleware('permission:attendance_summary.view')->name('summaries');
    Route::get('defaulters', [AttendanceModuleController::class, 'defaulters'])->middleware('permission:attendance_defaulter.view')->name('defaulters');
});
