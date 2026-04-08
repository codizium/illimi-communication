<?php

use Illimi\Communication\Controllers\V1\ConversationController;
use Illimi\Communication\Controllers\V1\EventController;
use Illimi\Communication\Controllers\V1\NoticeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/communication')->middleware(['web', 'auth:sanctum', 'organization'])->group(function () {
    Route::post('presence/heartbeat', [ConversationController::class, 'heartbeat'])->name('v1.communication.presence.heartbeat');
    Route::get('events', [EventController::class, 'index'])->name('v1.communication.events.index');
    Route::post('events', [EventController::class, 'store'])->name('v1.communication.events.store');
    Route::get('notices', [NoticeController::class, 'index'])->name('v1.communication.notices.index');
    Route::post('notices', [NoticeController::class, 'store'])->name('v1.communication.notices.store');
    Route::get('conversations', [ConversationController::class, 'index'])->name('v1.communication.conversations.index');
    Route::post('conversations', [ConversationController::class, 'store'])->name('v1.communication.conversations.store');
    Route::get('conversations/{id}/messages', [ConversationController::class, 'messages'])->name('v1.communication.conversations.messages');
    Route::post('conversations/{id}/messages', [ConversationController::class, 'sendMessage'])->name('v1.communication.conversations.messages.store');
    Route::post('conversations/{id}/read', [ConversationController::class, 'markRead'])->name('v1.communication.conversations.read');
    Route::post('conversations/{id}/archive', [ConversationController::class, 'archive'])->name('v1.communication.conversations.archive');
});
