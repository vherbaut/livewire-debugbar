<?php

use Illuminate\Support\Facades\Route;
use Vherbaut\LivewireDebugbar\Http\Controllers\FileWatcherController;

Route::middleware('web')->group(function () {
    Route::prefix('livewire-debugbar')->group(function () {
        Route::get('watched-files', [FileWatcherController::class, 'getWatchedFiles'])
            ->name('livewire-debugbar.watched-files');
    });
});
