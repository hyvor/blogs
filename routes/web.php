<?php

use App\Events\TestEvent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

include 'testing.php';
if (App::environment('local')) {
    include 'local.php';
}

include 'blog.php';

// main app
Route::domain(config('blogs.domain_app'))->group(function () {
    include 'app/pages.php';
    include 'app/api-data.php';
    include 'app/api-console.php';
    include 'app/api-cli.php';
    include 'app/special.php';
    include 'app/integrations/integrations.php';
});

Route::get('/fire', function () {
    TestEvent::dispatch('Hello World!');

    return 'Event has been sent!';
});


include 'app/api-delivery.php';
