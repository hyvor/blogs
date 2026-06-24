<?php

use Illuminate\Support\Facades\Route;

// main app
Route::domain(config('blogs.domain_app'))->group(function () {
    include 'app/api-console.php';
    include 'app/special.php';
});

include 'app/api-delivery.php';
