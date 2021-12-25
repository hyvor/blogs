<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\PostController;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;


include('app/subdomain.php');

Route::domain(config('app.domain_app'))->group(function() {
    
    include('app/pages.php');
    include('app/api-data.php');
    include('app/api-delivery.php');
    include('app/api-console.php');

});


// Delevery Routes
// Route::domain('{subdomain}.hyvorblogs.test')->middleware('blogDeliver')->group(function () {

//     // Delevery Routes
//     include('Delivery/api-assets.php');
//     include('Delivery/api-theme.php');

//     // Select a specific theme for the blog
//     Route::get('/theme', [App\Http\Controllers\ThemesController::class, 'selectTheme'])->name('/theme');

// });