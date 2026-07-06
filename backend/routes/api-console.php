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

            // link analysis
            Route::post('/link-analysis/check-urls', [ConsoleLinkAnalysisController::class, 'checkPostVariantLinks']);
            Route::patch('link-analysis/ignore-link', [ConsoleLinkAnalysisController::class, 'ignoreLink']);
            Route::get('/link-analysis/stats', [ConsoleLinkAnalysisController::class, 'getStats']);
            Route::get('/link-analysis/links', [ConsoleLinkAnalysisController::class, 'getLinks']);
            Route::get('/link-analysis/checks', [ConsoleLinkAnalysisController::class, 'getChecks']);
            Route::post('/link-analysis/check', [ConsoleLinkAnalysisController::class, 'startCheck']);

            // GPT
            Route::post('/gpt/prompt', [ConsoleGptController::class, 'newPrompt']);
            Route::get('/gpt/post-history', [ConsoleGptController::class, 'getPostChatHistory']);
            Route::delete('/gpt/post-history', [ConsoleGptController::class, 'deletePostChatHistory']);
        });

        /**
         * Settings, users, and theme
         */
        Route::middleware('role:owner|admin')->group(function () {

            // import and export
            Route::get('/data/exports', [ConsoleExportController::class, 'getExports']);
            Route::post('/data/export', [ConsoleExportController::class, 'export']);

            Route::get('/data/imports', [ConsoleImportController::class, 'getImports']);
            Route::post('/data/import/sitemap/test', [ConsoleImportSitemapController::class, 'test']);
            Route::post('/data/import/sitemap/import', [ConsoleImportSitemapController::class, 'import']);
        });
    });
