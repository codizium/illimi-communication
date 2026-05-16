<?php

use Illimi\Communication\Controllers\V1\ConversationController;
use Illimi\Communication\Controllers\V1\EventController;
use Illimi\Communication\Controllers\V1\NoticeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/communication')
    ->name('v1.communication.')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function () {
    Route::post('presence/heartbeat', [ConversationController::class, 'heartbeat'])
        ->middleware('throttle:60,1')
        ->name('presence.heartbeat');
    Route::get('users/search', [ConversationController::class, 'searchUsers'])
        ->middleware('throttle:30,1')
        ->name('users.search');

    Route::apiResource('events', EventController::class)
        ->middleware('throttle:60,1');
    Route::apiResource('notices', NoticeController::class)
        ->middleware('throttle:60,1');

    Route::apiResource('conversations', ConversationController::class)
        ->only(['index', 'store', 'destroy'])
        ->middleware('throttle:60,1');
    Route::get('conversations/{id}/messages', [ConversationController::class, 'messages'])
        ->middleware('throttle:60,1')
        ->name('conversations.messages');
    Route::post('conversations/{id}/messages', [ConversationController::class, 'sendMessage'])
        ->middleware('throttle:30,1')
        ->name('conversations.messages.store');
    Route::post('conversations/{id}/read', [ConversationController::class, 'markRead'])
        ->middleware('throttle:60,1')
        ->name('conversations.read');
    Route::post('conversations/{id}/archive', [ConversationController::class, 'archive'])
        ->middleware('throttle:10,1')
        ->name('conversations.archive');
    Route::post('conversations/{id}/clear-messages', [ConversationController::class, 'clearMessages'])
        ->middleware('throttle:5,1')
        ->name('conversations.clear');

    Route::get('notifications', [\Illimi\Communication\Controllers\V1\NotificationController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('notifications.index');
    Route::get('notifications/unread-count', [\Illimi\Communication\Controllers\V1\NotificationController::class, 'unreadCount'])
        ->middleware('throttle:60,1')
        ->name('notifications.unread-count');
    Route::post('notifications/{id}/read', [\Illimi\Communication\Controllers\V1\NotificationController::class, 'markAsRead'])
        ->middleware('throttle:30,1')
        ->name('notifications.read');
    Route::post('notifications/read-all', [\Illimi\Communication\Controllers\V1\NotificationController::class, 'markAllAsRead'])
        ->middleware('throttle:10,1')
        ->name('notifications.read-all');
});
