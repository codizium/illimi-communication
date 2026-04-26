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
    Route::get('users/search', [ConversationController::class, 'searchUsers'])->name('users.search');
    Route::apiResource('events', EventController::class);
    Route::apiResource('notices', NoticeController::class);
    Route::apiResource('conversations', ConversationController::class)->only(['index', 'store', 'destroy']);
    Route::get('conversations/{id}/messages', [ConversationController::class, 'messages'])->name('conversations.messages');
    Route::post('conversations/{id}/messages', [ConversationController::class, 'sendMessage'])->name('conversations.messages.store');
    Route::post('conversations/{id}/read', [ConversationController::class, 'markRead'])->name('conversations.read');
    Route::post('conversations/{id}/archive', [ConversationController::class, 'archive'])->name('conversations.archive');
    Route::post('conversations/{id}/clear-messages', [ConversationController::class, 'clearMessages'])->name('conversations.clear');

    Route::get('notifications', [\Illimi\Communication\Controllers\V1\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [\Illimi\Communication\Controllers\V1\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('notifications/{id}/read', [\Illimi\Communication\Controllers\V1\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [\Illimi\Communication\Controllers\V1\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});
