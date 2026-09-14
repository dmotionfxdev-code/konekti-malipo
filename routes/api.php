<?php

use App\Http\Controllers\Api\PaymentCallbackController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/payments', [PaymentController::class, 'store'])->middleware(['api.key', 'throttle:30,1']);
Route::match(['get', 'post'], '/payments/webhooks/{gateway}', [PaymentCallbackController::class, 'webhook'])->middleware('throttle:30,1');
Route::get('/payments/callback/{gateway}', [PaymentCallbackController::class, 'callback'])->name('payments.callback')->middleware('throttle:30,1');
Route::match(['get', 'post'], '/payments/webhooks/{gateway}/accounts/{account:uuid}', [PaymentCallbackController::class, 'accountWebhook'])->name('payments.webhook.account')->middleware('throttle:30,1');
Route::get('/payments/callback/{gateway}/accounts/{account:uuid}', [PaymentCallbackController::class, 'accountCallback'])->name('payments.callback.account')->middleware('throttle:30,1');
