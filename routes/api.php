<?php

use Illimi\Communication\Controllers\V1\ConversationController;
use Illimi\Communication\Controllers\V1\EventController;
use Illimi\Communication\Controllers\V1\NoticeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/communication')
    ->name('v1.communication.')
    ->middleware(['api', 'auth:sanctum', 'organization'])
    ->group(function () {
    Route::post('presence/heartbeat', [ConversationController::class, 'heartbeat'])->name('presence.heartbeat');
    Route::apiResource('events', EventController::class)->only(['index', 'store']);
    Route::apiResource('notices', NoticeController::class)->only(['index', 'store']);
    Route::apiResource('conversations', ConversationController::class)->only(['index', 'store']);
    Route::get('conversations/{id}/messages', [ConversationController::class, 'messages'])->name('conversations.messages');
    Route::post('conversations/{id}/messages', [ConversationController::class, 'sendMessage'])->name('conversations.messages.store');
    Route::post('conversations/{id}/read', [ConversationController::class, 'markRead'])->name('conversations.read');
    Route::post('conversations/{id}/archive', [ConversationController::class, 'archive'])->name('conversations.archive');
});
