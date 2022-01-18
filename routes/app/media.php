<?php

use App\Http\Controllers\Media\EmbedController;
use Illuminate\Support\Facades\Route;

Route::get('/embed', [EmbedController::class, 'embedRichIframe']);