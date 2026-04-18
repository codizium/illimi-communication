<?php

use Illimi\Communication\Controllers\Web\CommunicationWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'organization'])
    ->prefix('communication')
    ->name('communication.')
    ->group(function () {
        Route::get('/messenger', [CommunicationWebController::class, 'messenger'])->name('messenger');
        Route::get('/events', [CommunicationWebController::class, 'events'])->name('events');
        Route::get('/noticeboard', [CommunicationWebController::class, 'noticeboard'])->name('noticeboard');
    });
