<?php

use App\Jobs\Scheduled\Counts\BlogCountJob;
use Illuminate\Support\Facades\Route;

Route::get('/run-blog-counts', function() {
    dispatch(new BlogCountJob);
});