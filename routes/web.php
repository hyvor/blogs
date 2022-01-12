<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\PostController;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;


// include('app/subdomain.php');

include('app/api-delivery.php');


Route::domain(config('app.domain_app'))->group(function() {
    
    include('app/pages.php');
    include('app/api-data.php');
    include('app/api-delivery.php');
    include('app/api-console.php');

});