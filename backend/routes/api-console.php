<?php

declare(strict_types=1);

use App\Http\Controllers\ConsoleAPI\ConsoleAiController;
use App\Http\Controllers\ConsoleAPI\ConsoleGptController;

Route::prefix('/api/console/v0/blog/{subdomain}')
    ->group(function () {
        /**
         * Posts and media
         */
        Route::middleware('role:owner|admin|editor|writer|contributor')->group(function () {
            Route::post('/ai/translate', [ConsoleAiController::class, 'translate']);

            // GPT
            Route::post('/gpt/prompt', [ConsoleGptController::class, 'newPrompt']);
            Route::get('/gpt/post-history', [ConsoleGptController::class, 'getPostChatHistory']);
            Route::delete('/gpt/post-history', [ConsoleGptController::class, 'deletePostChatHistory']);
        });
    });
