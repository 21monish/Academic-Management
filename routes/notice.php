<?php

use App\Http\Controllers\Notice\NoticeModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('notices')->name('notices.')->group(function () {
    Route::get('categories', [NoticeModuleController::class, 'categories'])->middleware('permission:notice_category.view,notice_category.create,notice_category.update')->name('categories');
    Route::post('categories', [NoticeModuleController::class, 'storeCategory'])->middleware('permission:notice_category.create')->name('categories.store');
    Route::delete('categories/{category}', [NoticeModuleController::class, 'destroyCategory'])->middleware('permission:notice_category.delete')->name('categories.destroy');

    Route::get('/', [NoticeModuleController::class, 'notices'])->middleware('permission:notice.view')->name('index');
    Route::post('/', [NoticeModuleController::class, 'storeNotice'])->middleware('permission:notice.create')->name('store');
    Route::delete('{notice}', [NoticeModuleController::class, 'destroyNotice'])->middleware('permission:notice.delete')->name('destroy');

    Route::get('audiences', [NoticeModuleController::class, 'audiences'])->middleware('permission:notice_audience.view,notice_audience.create,notice_audience.update,notice_audience.approve')->name('audiences');
    Route::post('audiences', [NoticeModuleController::class, 'storeAudience'])->middleware('permission:notice_audience.create,notice_audience.approve')->name('audiences.store');
    Route::delete('audiences/{audience}', [NoticeModuleController::class, 'destroyAudience'])->middleware('permission:notice_audience.delete')->name('audiences.destroy');

    Route::get('attachments', [NoticeModuleController::class, 'attachments'])->middleware('permission:notice_attachment.view,notice_attachment.create,notice_attachment.update')->name('attachments');
    Route::post('attachments', [NoticeModuleController::class, 'storeAttachment'])->middleware('permission:notice_attachment.create,notice_attachment.update')->name('attachments.store');
    Route::delete('attachments/{attachment}', [NoticeModuleController::class, 'destroyAttachment'])->middleware('permission:notice_attachment.delete')->name('attachments.destroy');

    Route::get('acknowledgements', [NoticeModuleController::class, 'acknowledgements'])->middleware('permission:notice_acknowledgement.view,notice_acknowledgement.create,notice_acknowledgement.update,notice_acknowledgement.approve')->name('acknowledgements');
    Route::post('acknowledgements', [NoticeModuleController::class, 'storeAcknowledgement'])->middleware('permission:notice_acknowledgement.create,notice_acknowledgement.update,notice_acknowledgement.approve')->name('acknowledgements.store');
    Route::delete('acknowledgements/{acknowledgement}', [NoticeModuleController::class, 'destroyAcknowledgement'])->middleware('permission:notice_acknowledgement.delete')->name('acknowledgements.destroy');
});
