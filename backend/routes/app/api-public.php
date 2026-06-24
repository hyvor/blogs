<?php

use App\Http\PublicApi\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/api/media/{path}', [MediaController::class, 'serve'])->where('path', '.*');