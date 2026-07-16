<?php

use App\Http\Controllers\Reports\ReportsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('students', [ReportsController::class, 'students'])->middleware('permission:student_report.view')->name('students');
    Route::get('students/{student}/print', [ReportsController::class, 'studentPrint'])->middleware('permission:student_report.view')->name('students.print');

    Route::get('attendance', [ReportsController::class, 'attendance'])->middleware('permission:attendance_report.view')->name('attendance');

    Route::get('results', [ReportsController::class, 'resultCards'])->middleware('permission:result_card.view')->name('results');
    Route::get('results/{student}/print', [ReportsController::class, 'resultPrint'])->middleware('permission:result_card.view')->name('results.print');

    Route::get('fee-receipts', [ReportsController::class, 'feeReceipts'])->middleware('permission:fee_receipt_report.view')->name('fee-receipts');
    Route::get('fee-receipts/{payment}/print', [ReportsController::class, 'receiptPrint'])->middleware('permission:fee_receipt_report.view')->name('fee-receipts.print');

    Route::get('hall-tickets', [ReportsController::class, 'hallTickets'])->middleware('permission:hall_ticket_report.view')->name('hall-tickets');
    Route::get('hall-tickets/{ticket}/print', [ReportsController::class, 'hallTicketPrint'])->middleware('permission:hall_ticket_report.view')->name('hall-tickets.print');

    Route::get('staff', [ReportsController::class, 'staff'])->middleware('permission:staff_report.view')->name('staff');
    Route::get('staff/{staff}/print', [ReportsController::class, 'staffPrint'])->middleware('permission:staff_report.view')->name('staff.print');

    Route::get('certificates', [ReportsController::class, 'certificates'])->middleware('permission:certificate.view')->name('certificates');
    Route::get('certificates/{student}/{type}', [ReportsController::class, 'certificatePrint'])
        ->where('type', 'bonafide|leaving|fee|transfer')
        ->middleware('permission:certificate.view')
        ->name('certificates.print');

    Route::get('activity', [ReportsController::class, 'activity'])->middleware('permission:activity_log.view')->name('activity');

    Route::get('export/{type}', [ReportsController::class, 'export'])->name('export');
});
