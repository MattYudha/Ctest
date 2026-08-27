<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WaWebhookController;
use App\Http\Controllers\Api\CrmContactApiController;

// route for catching location from whatsapp bot
Route::post('/webhook/wa-checkout', [WaWebhookController::class, 'handleCheckout']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('crm/contacts', CrmContactApiController::class);
});
