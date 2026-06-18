<?php

use Illuminate\Support\Facades\Route;

// main app
Route::domain(config('blogs.domain_app'))->group(function () {
    include 'app/api-console.php';
    include 'app/api-cli.php';
    include 'app/api-public.php';
    include 'app/special.php';
});

// local routes
Route::domain('localhost')->group(function () {
    include 'app/special.php';
});

include 'app/api-delivery.php';
