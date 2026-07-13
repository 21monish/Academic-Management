<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('chatbot')->name('chatbot.')->group(function () {
    Route::post('/ask', [ChatbotController::class, 'ask'])->middleware('permission:chatbot.ask')->name('ask');
    Route::post('/teach', [ChatbotController::class, 'teach'])->middleware('permission:chatbot.teach')->name('teach');
    Route::delete('/knowledge/{knowledge}', [ChatbotController::class, 'forget'])->middleware('permission:chatbot.teach')->name('knowledge.forget');
});
