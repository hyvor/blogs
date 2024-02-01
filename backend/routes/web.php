<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

include 'testing.php';
if (App::environment('local')) {
    include 'local.php';
}


// main app
Route::domain(config('blogs.domain_app'))->group(function () {
    include 'app/api-data.php';
    include 'app/api-console.php';
    include 'app/api-cli.php';
    include 'app/special.php';
    include 'app/integrations/integrations.php';
    include 'blog.php';
});

include 'app/api-delivery.php';
