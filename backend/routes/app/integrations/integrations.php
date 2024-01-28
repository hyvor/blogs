<?php


use Illuminate\Support\Facades\Route;

Route::prefix('/integrations')->group(function () {
    include 'paddle.php';
    include 'shopify.php';
});
