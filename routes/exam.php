<?php

use App\Http\Controllers\Exam\ExamModuleController;
use App\Http\Controllers\Exam\ExamLogisticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [ExamModuleController::class, 'index'])->middleware('permission:exam.view')->name('index');
    Route::post('/', [ExamModuleController::class, 'storeExam'])->middleware('permission:exam.create')->name('store');
    Route::delete('/{exam}', [ExamModuleController::class, 'destroyExam'])->middleware('permission:exam.delete')->name('destroy');

    Route::get('subjects', [ExamModuleController::class, 'subjects'])->middleware('permission:exam_subject.view')->name('subjects');
    Route::post('subjects', [ExamModuleController::class, 'storeSubject'])->middleware('permission:exam_subject.create')->name('subjects.store');
    Route::delete('subjects/{examSubject}', [ExamModuleController::class, 'destroySubject'])->middleware('permission:exam_subject.delete')->name('subjects.destroy');

    Route::get('grades', [ExamModuleController::class, 'grades'])->middleware('permission:grade.view,grade.create')->name('grades');
    Route::post('grades', [ExamModuleController::class, 'storeGrade'])->middleware('permission:grade.create')->name('grades.store');
    Route::delete('grades/{grade}', [ExamModuleController::class, 'destroyGrade'])->middleware('permission:grade.delete,grade.update')->name('grades.destroy');

    Route::get('marks', [ExamModuleController::class, 'marks'])->middleware('permission:marks_entry.create,marks_entry.update')->name('marks');
    Route::post('marks', [ExamModuleController::class, 'storeMarks'])->middleware('permission:marks_entry.create,marks_entry.update')->name('marks.store');
    Route::get('results', [ExamModuleController::class, 'results'])->middleware('permission:result.view')->name('results');

    Route::get('backlogs', [ExamModuleController::class, 'backlogs'])->middleware('permission:backlog.view')->name('backlogs');
    Route::post('backlogs', [ExamModuleController::class, 'storeBacklog'])->middleware('permission:backlog.create')->name('backlogs.store');
    Route::delete('backlogs/{backlog}', [ExamModuleController::class, 'destroyBacklog'])->middleware('permission:backlog.delete,backlog.update')->name('backlogs.destroy');

    Route::get('promotions', [ExamModuleController::class, 'promotions'])->middleware('permission:promotion.view')->name('promotions');
    Route::post('promotions', [ExamModuleController::class, 'storePromotion'])->middleware('permission:promotion.create,promotion.approve')->name('promotions.store');
    Route::delete('promotions/{promotion}', [ExamModuleController::class, 'destroyPromotion'])->middleware('permission:promotion.delete,promotion.update')->name('promotions.destroy');

    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('hall-ticket-configs', [ExamLogisticsController::class, 'configs'])->middleware('permission:hall_ticket_config.view')->name('configs');
        Route::post('hall-ticket-configs', [ExamLogisticsController::class, 'storeConfig'])->middleware('permission:hall_ticket_config.create,hall_ticket_config.update')->name('configs.store');
        Route::delete('hall-ticket-configs/{config}', [ExamLogisticsController::class, 'destroyConfig'])->middleware('permission:hall_ticket_config.delete,hall_ticket_config.update')->name('configs.destroy');

        Route::get('hall-tickets', [ExamLogisticsController::class, 'tickets'])->middleware('permission:hall_ticket.view')->name('tickets');
        Route::post('hall-tickets/generate', [ExamLogisticsController::class, 'generateTickets'])->middleware('permission:hall_ticket.create')->name('tickets.generate');

        Route::get('rooms', [ExamLogisticsController::class, 'rooms'])->middleware('permission:exam_room.view')->name('rooms');
        Route::post('rooms', [ExamLogisticsController::class, 'storeRoom'])->middleware('permission:exam_room.create,exam_room.update')->name('rooms.store');
        Route::delete('rooms/{room}', [ExamLogisticsController::class, 'destroyRoom'])->middleware('permission:exam_room.delete,exam_room.update')->name('rooms.destroy');

        Route::get('seating', [ExamLogisticsController::class, 'seating'])->middleware('permission:seating.view')->name('seating');
        Route::post('seating', [ExamLogisticsController::class, 'storeSeating'])->middleware('permission:seating.create,seating.update')->name('seating.store');
        Route::delete('seating/{seating}', [ExamLogisticsController::class, 'destroySeating'])->middleware('permission:seating.delete,seating.update')->name('seating.destroy');

        Route::get('invigilators', [ExamLogisticsController::class, 'invigilators'])->middleware('permission:invigilator.view')->name('invigilators');
        Route::post('invigilators', [ExamLogisticsController::class, 'storeInvigilator'])->middleware('permission:invigilator.create,invigilator.update')->name('invigilators.store');
        Route::delete('invigilators/{duty}', [ExamLogisticsController::class, 'destroyInvigilator'])->middleware('permission:invigilator.delete,invigilator.update')->name('invigilators.destroy');

        Route::get('practical-schedules', [ExamLogisticsController::class, 'practicalSchedules'])->middleware('permission:practical_schedule.view')->name('practical-schedules');
        Route::post('practical-schedules', [ExamLogisticsController::class, 'storePracticalSchedule'])->middleware('permission:practical_schedule.create,practical_schedule.update')->name('practical-schedules.store');
        Route::delete('practical-schedules/{schedule}', [ExamLogisticsController::class, 'destroyPracticalSchedule'])->middleware('permission:practical_schedule.delete,practical_schedule.update')->name('practical-schedules.destroy');

        Route::get('practical-batches', [ExamLogisticsController::class, 'practicalBatches'])->middleware('permission:practical_batch.view')->name('practical-batches');
        Route::post('practical-batches', [ExamLogisticsController::class, 'storePracticalBatch'])->middleware('permission:practical_batch.create,practical_batch.update')->name('practical-batches.store');
        Route::post('practical-batches/students', [ExamLogisticsController::class, 'storePracticalBatchStudent'])->middleware('permission:practical_batch.create,practical_batch.update')->name('practical-batches.students.store');
        Route::delete('practical-batches/{batch}', [ExamLogisticsController::class, 'destroyPracticalBatch'])->middleware('permission:practical_batch.delete,practical_batch.update')->name('practical-batches.destroy');

        Route::get('practical-marks', [ExamLogisticsController::class, 'practicalMarks'])->middleware('permission:practical_mark.view,practical_mark.create')->name('practical-marks');
        Route::post('practical-marks', [ExamLogisticsController::class, 'storePracticalMarks'])->middleware('permission:practical_mark.create,practical_mark.update')->name('practical-marks.store');
        Route::delete('practical-marks/{mark}', [ExamLogisticsController::class, 'destroyPracticalMarks'])->middleware('permission:practical_mark.delete,practical_mark.update')->name('practical-marks.destroy');
    });
});
