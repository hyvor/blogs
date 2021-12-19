<?php

use Illuminate\Support\Facades\Route;

include('app/subdomain.php');

Route::domain(config('app.domain_app'))->group(function() {
    
    include('app/pages.php');

    include('app/api-data.php');
    include('app/api-delivery.php');
    include('app/api-console.php');

});