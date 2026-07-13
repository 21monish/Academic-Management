<?php

use App\Http\Controllers\PasswordChangeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Add to routes/web.php (inside the 'auth' middleware group, or standalone
| since it must be reachable even when EnsurePasswordIsChanged is blocking
| everything else):
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'show'])
        ->middleware('permission:password_change.view')
        ->name('password.change.show');
    Route::put('/change-password', [PasswordChangeController::class, 'update'])
        ->middleware('permission:password_change.update')
        ->name('password.change.update');
});
