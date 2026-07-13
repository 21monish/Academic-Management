<?php

use App\Http\Controllers\Fees\FeeModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('fees')->name('fees.')->group(function () {
    Route::get('categories', [FeeModuleController::class, 'categories'])->middleware('permission:fee_category.view')->name('categories');
    Route::post('categories', [FeeModuleController::class, 'storeCategory'])->middleware('permission:fee_category.create')->name('categories.store');
    Route::delete('categories/{category}', [FeeModuleController::class, 'destroyCategory'])->middleware('permission:fee_category.delete')->name('categories.destroy');

    Route::get('structures', [FeeModuleController::class, 'structures'])->middleware('permission:fee_structure.view')->name('structures');
    Route::post('structures', [FeeModuleController::class, 'storeStructure'])->middleware('permission:fee_structure.create')->name('structures.store');
    Route::delete('structures/{structure}', [FeeModuleController::class, 'destroyStructure'])->middleware('permission:fee_structure.delete')->name('structures.destroy');

    Route::get('ledgers', [FeeModuleController::class, 'ledgers'])->middleware('permission:student_ledger.view')->name('ledgers');
    Route::post('ledgers', [FeeModuleController::class, 'storeLedger'])->middleware('permission:student_ledger.create')->name('ledgers.store');
    Route::delete('ledgers/{ledger}', [FeeModuleController::class, 'destroyLedger'])->middleware('permission:student_ledger.delete')->name('ledgers.destroy');

    Route::get('collections', [FeeModuleController::class, 'collections'])->middleware('permission:fee_collection.view,fee_collection.create,fee_collection.update')->name('collections');
    Route::post('collections', [FeeModuleController::class, 'storeCollection'])->middleware('permission:fee_collection.create')->name('collections.store');
    Route::delete('collections/{payment}', [FeeModuleController::class, 'destroyCollection'])->middleware('permission:fee_collection.delete')->name('collections.destroy');

    Route::get('receipts', [FeeModuleController::class, 'receipts'])->middleware('permission:receipt.view')->name('receipts');

    Route::get('concessions', [FeeModuleController::class, 'concessions'])->middleware('permission:concession.view,concession.approve')->name('concessions');
    Route::post('concessions', [FeeModuleController::class, 'storeConcession'])->middleware('permission:concession.create,concession.approve')->name('concessions.store');
    Route::delete('concessions/{concession}', [FeeModuleController::class, 'destroyConcession'])->middleware('permission:concession.delete')->name('concessions.destroy');

    Route::get('scholarships', [FeeModuleController::class, 'scholarships'])->middleware('permission:scholarship.view')->name('scholarships');
    Route::post('scholarships', [FeeModuleController::class, 'storeScholarship'])->middleware('permission:scholarship.create')->name('scholarships.store');
    Route::delete('scholarships/{scholarship}', [FeeModuleController::class, 'destroyScholarship'])->middleware('permission:scholarship.delete')->name('scholarships.destroy');

    Route::get('reports', [FeeModuleController::class, 'reports'])->middleware('permission:fee_report.view')->name('reports');
});
