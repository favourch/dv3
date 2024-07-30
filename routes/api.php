<?php

use App\Http\Middleware\AuthenticateBearerToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::middleware([AuthenticateBearerToken::class])->group(function () {
    Route::post('/send', [App\Http\Controllers\ApiController::class, 'sendMessage']);
    Route::post('/campaigns', [App\Http\Controllers\ApiController::class, 'storeCampaign']);
    
    Route::get('/contacts', [App\Http\Controllers\ApiController::class, 'listContacts']);
    Route::post('/contacts', [App\Http\Controllers\ApiController::class, 'storeContact']);
    Route::put('/contacts/{uuid}', [App\Http\Controllers\ApiController::class, 'storeContact']);
    Route::delete('/contacts/{uuid}', [App\Http\Controllers\ApiController::class, 'destroyContact']);

    Route::get('/contact-groups', [App\Http\Controllers\ApiController::class, 'listContactGroups']);
    Route::post('/contact-groups', [App\Http\Controllers\ApiController::class, 'storeContactGroup']);
    Route::put('/contact-groups/{uuid}', [App\Http\Controllers\ApiController::class, 'storeContactGroup']);
    Route::delete('/contact-groups/{uuid}', [App\Http\Controllers\ApiController::class, 'destroyContactGroup']);

    Route::get('/canned-replies', [App\Http\Controllers\ApiController::class, 'listCannedReplies']);
    Route::post('/canned-replies', [App\Http\Controllers\ApiController::class, 'storeCannedReply']);
    Route::put('/canned-replies/{uuid}', [App\Http\Controllers\ApiController::class, 'storeCannedReply']);
    Route::delete('/canned-replies/{uuid}', [App\Http\Controllers\ApiController::class, 'destroyCannedReply']);
});
