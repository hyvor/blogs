<?php

declare(strict_types=1);

use App\Http\ConsoleApi\Controllers\ConsoleController;
use App\Http\ConsoleApi\Middleware\ConsoleApiAuthMiddleware;
use App\Http\Controllers\ConsoleAPI\ConsoleAiController;
use App\Http\Controllers\ConsoleAPI\ConsoleDangerController;
use App\Http\Controllers\ConsoleAPI\ConsoleExportController;
use App\Http\Controllers\ConsoleAPI\ConsoleGptController;
use App\Http\Controllers\ConsoleAPI\ConsoleLinkAnalysisController;
use App\Http\Controllers\ConsoleAPI\ConsoleMediaController;
use App\Http\Controllers\ConsoleAPI\ConsoleThemeController;
use App\Http\Controllers\ConsoleAPI\ConsoleUrlDataController;
use App\Http\Controllers\ConsoleAPI\ConsoleUserBlogController;
use App\Http\Controllers\ConsoleAPI\ConsoleUserController;
use App\Http\Controllers\ConsoleAPI\Import\ConsoleImportController;
use App\Http\Controllers\ConsoleAPI\Import\ConsoleImportSitemapController;
use App\Http\Controllers\ConsoleAPI\Integrations\IntegrationHyvorTalkController;
use App\Http\Controllers\ConsoleAPI\Misc\ConsoleMiscProsemirrorController;
use App\Http\Middleware\App\ConsoleApi\ConsoleApiAccessMiddleware;
use App\Http\Middleware\App\ConsoleApi\ResourceAccessMiddleware;
use App\Http\Middleware\App\SubdomainMiddleware;
use App\Http\Middleware\CorsOnLocalhost;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/console/v0')
    ->middleware([
        CorsOnLocalhost::class,
    ])
    ->group(function () {
        Route::get('/init-temp', [ConsoleController::class, 'initTemp']);
    });

Route::prefix('/api/console/v0')
    ->middleware([
        ConsoleApiAuthMiddleware::class,
        CorsOnLocalhost::class,
    ])
    ->group(function () {
        Route::post('/blog', [ConsoleUserBlogController::class, 'createBlog']);
    });

/**
 * Console API
 * ======================
 *
 * this is the Console API
 * can be used by both us and others
 * Important! see BlogAccessMiddleware to see how to write these routes securely
 */
Route::prefix('/api/console/v0/blog/{subdomain}')
    ->middleware([
        // converts {subdomain} tp Blog model
        SubdomainMiddleware::class,

        // check if the user or API key has access to the console API
        // and set App\Models\User app instance
        ConsoleApiAccessMiddleware::class,

        // checks relationship to the blog, for resources that have {id} in route
        ResourceAccessMiddleware::class,

        CorsOnLocalhost::class,
    ])
    ->group(function () {
        /**
         * Posts and media
         */
        Route::middleware('role:owner|admin|editor|writer|contributor')->group(function () {
            // media CRD
            Route::get('/media', [ConsoleMediaController::class, 'getMedia']);
            Route::post('/media', [ConsoleMediaController::class, 'uploadFile']);
            Route::post('/media/from-url', [ConsoleMediaController::class, 'uploadFileFromUrl']);
            Route::patch('/media/{id}', [ConsoleMediaController::class, 'updateMedia']);
            Route::delete('/media/{id}', [ConsoleMediaController::class, 'deleteFile']);
            Route::get('/media/unsplash/search', [ConsoleMediaController::class, 'searchUnsplash']);

            // url data
            Route::get('/url-data', [ConsoleUrlDataController::class, 'getData']);

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

            // users
            Route::get('/users', [ConsoleUserController::class, 'get']);
            Route::get('/users/search', [ConsoleUserController::class, 'search']);
            Route::post('/user', [ConsoleUserController::class, 'create']);
            Route::post('/user/guest', [ConsoleUserController::class, 'createGuest']);
            Route::patch('/user/{id}', [ConsoleUserController::class, 'update']);
            Route::delete('/user/{id}', [ConsoleUserController::class, 'delete']);
            Route::get('/user/{id}/slug-available', [ConsoleUserController::class, 'checkSlugAvailability']);
            Route::post('/user/{id}/variant', [ConsoleUserController::class, 'createVariant']);
            Route::patch('/user/{id}/variant', [ConsoleUserController::class, 'updateVariant']);
            Route::delete('/user/{id}/variant', [ConsoleUserController::class, 'deleteVariant']);
            Route::post('/user/{id}/resend-invite', [ConsoleUserController::class, 'resendInvite']);

            // theme
            Route::post('/theme', [ConsoleThemeController::class, 'uploadTheme']);
            Route::patch('/theme', [ConsoleThemeController::class, 'changeTheme']);
            Route::get('/theme/download', [ConsoleThemeController::class, 'downloadTheme']);
            Route::get('/theme/files', [ConsoleThemeController::class, 'getAllFiles']);
            Route::post('/theme/file', [ConsoleThemeController::class, 'createFile']);
            Route::patch('/theme/file/{id}', [ConsoleThemeController::class, 'updateFile']);
            Route::delete('/theme/file/{id}', [ConsoleThemeController::class, 'deleteFile']);
            Route::get('/theme/file/name-available', [ConsoleThemeController::class, 'isFileNameAvailable']);

            // import and export
            Route::get('/data/exports', [ConsoleExportController::class, 'getExports']);
            Route::post('/data/export', [ConsoleExportController::class, 'export']);

            Route::get('/data/imports', [ConsoleImportController::class, 'getImports']);
            Route::post('/data/import/sitemap/test', [ConsoleImportSitemapController::class, 'test']);
            Route::post('/data/import/sitemap/import', [ConsoleImportSitemapController::class, 'import']);
        });

        /**
         * Integrations
         */
        Route::middleware('role:owner|admin')
            ->prefix('integrations')
            ->group(function () {
                Route::get('/hyvor-talk', [IntegrationHyvorTalkController::class, 'getIntegration']);
                Route::post('/hyvor-talk', [IntegrationHyvorTalkController::class, 'createIntegration']);
                Route::delete('/hyvor-talk', [IntegrationHyvorTalkController::class, 'deleteIntegration']);
                Route::get(
                    '/hyvor-talk/gated-content-rules',
                    [IntegrationHyvorTalkController::class, 'getGatedContentRules'],
                );
                Route::post(
                    '/hyvor-talk/gated-content-rule',
                    [IntegrationHyvorTalkController::class, 'createGatedContentRule'],
                );
                Route::patch(
                    '/hyvor-talk/gated-content-rule/{id}',
                    [IntegrationHyvorTalkController::class, 'updateGatedContentRule'],
                );
                Route::delete(
                    '/hyvor-talk/gated-content-rule/{id}',
                    [IntegrationHyvorTalkController::class, 'deleteGatedContentRule'],
                );

                Route::get(
                    '/hyvor-talk/membership-plans',
                    [IntegrationHyvorTalkController::class, 'getMembershipPlans'],
                );
            });

        /**
         * Misc
         */
        Route::prefix('misc')->group(function () {
            Route::get('/prosemirror/json', [ConsoleMiscProsemirrorController::class, 'getJson']);
        });

        /**
         * Danger
         */
        Route::middleware('role:owner')->group(function () {
            Route::delete('/blog', [ConsoleDangerController::class, 'delete']);
            // Route::post('/blog/reset', [ConsoleDangerController::class, 'reset']);
            Route::delete('/blog/cache', [ConsoleDangerController::class, 'deleteCache']);
        });
    });
