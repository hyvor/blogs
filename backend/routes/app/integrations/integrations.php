<?php


use Illuminate\Support\Facades\Route;

Route::prefix('/api/integrations')->group(function () {
    include 'paddle.php';
});
