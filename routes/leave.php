<?php

use App\Http\Controllers\Leave\LeaveModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('leave')->name('leave.')->group(function () {
    Route::get('types', [LeaveModuleController::class, 'types'])->middleware('permission:leave_type.view')->name('types');
    Route::post('types', [LeaveModuleController::class, 'storeType'])->middleware('permission:leave_type.create')->name('types.store');
    Route::delete('types/{type}', [LeaveModuleController::class, 'destroyType'])->middleware('permission:leave_type.delete')->name('types.destroy');

    Route::get('balances', [LeaveModuleController::class, 'balances'])->middleware('permission:leave_balance.view')->name('balances');
    Route::post('balances', [LeaveModuleController::class, 'storeBalance'])->middleware('permission:leave_balance.create,leave_balance.update')->name('balances.store');
    Route::delete('balances/{balance}', [LeaveModuleController::class, 'destroyBalance'])->middleware('permission:leave_balance.delete')->name('balances.destroy');

    Route::get('applications', [LeaveModuleController::class, 'applications'])->middleware('permission:leave_application.view')->name('applications');
    Route::post('applications', [LeaveModuleController::class, 'storeApplication'])->middleware('permission:leave_application.create')->name('applications.store');
    Route::delete('applications/{application}', [LeaveModuleController::class, 'destroyApplication'])->middleware('permission:leave_application.delete,leave_application.update')->name('applications.destroy');

    Route::get('approvals', [LeaveModuleController::class, 'approvals'])->middleware('permission:leave_approval.approve,leave_approval.update')->name('approvals');
    Route::post('approvals', [LeaveModuleController::class, 'storeApproval'])->middleware('permission:leave_approval.approve')->name('approvals.store');
    Route::delete('approvals/{approval}', [LeaveModuleController::class, 'destroyApproval'])->middleware('permission:leave_approval.delete,leave_approval.approve')->name('approvals.destroy');

    Route::get('cancellations', [LeaveModuleController::class, 'cancellations'])->middleware('permission:leave_cancellation.view')->name('cancellations');
    Route::post('cancellations', [LeaveModuleController::class, 'storeCancellation'])->middleware('permission:leave_cancellation.create,leave_cancellation.update')->name('cancellations.store');
    Route::delete('cancellations/{cancellation}', [LeaveModuleController::class, 'destroyCancellation'])->middleware('permission:leave_cancellation.delete')->name('cancellations.destroy');

    Route::get('substitutes', [LeaveModuleController::class, 'substitutes'])->middleware('permission:leave_substitute.view')->name('substitutes');
    Route::post('substitutes', [LeaveModuleController::class, 'storeSubstitute'])->middleware('permission:leave_substitute.create,leave_substitute.update')->name('substitutes.store');
    Route::delete('substitutes/{substitute}', [LeaveModuleController::class, 'destroySubstitute'])->middleware('permission:leave_substitute.delete')->name('substitutes.destroy');

    Route::get('holidays', [LeaveModuleController::class, 'holidays'])->middleware('permission:holiday.view')->name('holidays');
    Route::post('holidays', [LeaveModuleController::class, 'storeHoliday'])->middleware('permission:holiday.create,holiday.update')->name('holidays.store');
    Route::delete('holidays/{holiday}', [LeaveModuleController::class, 'destroyHoliday'])->middleware('permission:holiday.delete')->name('holidays.destroy');
});
