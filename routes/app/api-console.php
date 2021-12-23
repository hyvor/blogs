<?php

use App\Http\Controllers\ConsoleAPI\ConsoleViewController;
use Illuminate\Support\Facades\Route;


Route::get('/console/{any?}', ConsoleViewController::class)->where('any', '.*');