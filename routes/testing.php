<?php declare(strict_types=1);

use App\Http\Controllers\Testing\TestingController;
use Illuminate\Support\Facades\App as App;
use Illuminate\Support\Facades\Route as Route;

if (App::environment('local')) {

    Route::prefix('_testing')->group(function() {
        Route::post('truncate', [TestingController::class, 'truncate']);
        Route::post('factory', [TestingController::class, 'factory']);
    });

}