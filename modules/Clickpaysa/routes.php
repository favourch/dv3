<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::post('/addons/setup/clickpay', [Modules\Clickpaysa\Controllers\SetupController::class, 'store']);
    Route::put('/addons/setup/clickpay', [Modules\Clickpaysa\Controllers\SetupController::class, 'update']);
});