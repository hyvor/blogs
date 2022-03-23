<?php

use App\Domains\Post\Content\PostContentRepository;
use App\Jobs\Scheduled\Counts\BlogCountJob;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/run-blog-counts', function() {
    dispatch(new BlogCountJob);
});

Route::get('/tiptap', function() {
    PostContentRepository::getHtml('', Blog::find(1));
});