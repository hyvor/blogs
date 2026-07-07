<?php

declare(strict_types=1);

use App\Http\ConsoleApi\Middleware\ConsoleApiAuthMiddleware;
use App\Http\Controllers\ConsoleAPI\ConsoleAiController;
use App\Http\Controllers\ConsoleAPI\ConsoleDangerController;
use App\Http\Controllers\ConsoleAPI\ConsoleExportController;
use App\Http\Controllers\ConsoleAPI\ConsoleGptController;
use App\Http\Controllers\ConsoleAPI\ConsoleLinkAnalysisController;
use App\Http\Controllers\ConsoleAPI\ConsoleUserBlogController;
use App\Http\Controllers\ConsoleAPI\Import\ConsoleImportController;
use App\Http\Controllers\ConsoleAPI\Import\ConsoleImportSitemapController;
use App\Http\Middleware\App\ConsoleApi\ConsoleApiAccessMiddleware;
use App\Http\Middleware\App\ConsoleApi\ResourceAccessMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use App\Http\Middleware\CorsOnLocalhost;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/console/v0/blog/{subdomain}')
    ->middleware([
        SubdomainMiddleware::class,
        ConsoleApiAccessMiddleware::class,
        ResourceAccessMiddleware::class,
    ])
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
