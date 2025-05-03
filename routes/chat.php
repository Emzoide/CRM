<?php
// routes/chat.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\ContactController;
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Chat\TemplateController;
use App\Http\Controllers\Chat\TagController;
use App\Http\Controllers\Chat\WebhookEventController;
use App\Http\Controllers\Chat\WebhookController;

// Rutas de chat
Route::group([], function () {
    // Webhook de WhatsApp
    Route::get('webhook', [WebhookController::class, 'verify']);
    Route::post('webhook', [WebhookController::class, 'receive']);

    // Webhook events
    Route::post('webhook-events', [WebhookEventController::class, 'store']);

    // Conversations
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::get('conversations/{id}', [ConversationController::class, 'show']);

    // Messages (nested under conversations)
    Route::get('conversations/{id}/messages', [MessageController::class, 'index']);
    Route::post('conversations/{id}/messages', [MessageController::class, 'store']);
});

// Rutas de administración
Route::group(['prefix' => 'admin'], function () {
    // Contacts
    Route::get('contacts', [ContactController::class, 'index']);
    Route::get('contacts/{id}', [ContactController::class, 'show']);
    Route::post('contacts', [ContactController::class, 'store']);
    Route::put('contacts/{id}', [ContactController::class, 'update']);
    Route::delete('contacts/{id}', [ContactController::class, 'destroy']);

    // Conversations
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::get('conversations/{id}', [ConversationController::class, 'show']);

    // Templates
    Route::get('templates', [TemplateController::class, 'index']);
    Route::get('templates/{id}', [TemplateController::class, 'show']);
    Route::post('templates', [TemplateController::class, 'store']);
    Route::put('templates/{id}', [TemplateController::class, 'update']);
    Route::delete('templates/{id}', [TemplateController::class, 'destroy']);

    // Tags
    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{id}', [TagController::class, 'show']);
    Route::post('tags', [TagController::class, 'store']);
    Route::put('tags/{id}', [TagController::class, 'update']);
    Route::delete('tags/{id}', [TagController::class, 'destroy']);
});

Route::prefix('chat')->group(function () {
    Route::post('messages', [MessageController::class, 'store']);
});
