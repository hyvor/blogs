<?php

use App\Http\Controllers\Integrations\Paddle\PaddleWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/paddle/webhook', [PaddleWebhookController::class, 'handle']);