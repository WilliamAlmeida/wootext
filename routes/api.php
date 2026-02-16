<?php

use App\Http\Controllers\Webhooks\ChatwootWebhookController;
use App\Http\Controllers\Webhooks\EvolutionWebhookController;
use App\Http\Controllers\Webhooks\WahaWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Webhooks (sem auth — chamados pelos servidores externos)
Route::prefix('webhooks')->group(function () {
    Route::post('chatwoot', ChatwootWebhookController::class)->name('webhooks.chatwoot');
    Route::post('evolution', EvolutionWebhookController::class)->name('webhooks.evolution');
    Route::post('waha', WahaWebhookController::class)->name('webhooks.waha');
});
