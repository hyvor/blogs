<?php

use App\Domains\Post\Content\PostContentRepository;
use App\Models\Blog;
use Illuminate\Support\Facades\Route;

Route::get('callout', function() {

    $json = PostContentRepository::getJsonFromHtml("
        <aside data-emoji=\"💡\" style=\"background-color: #ffd969\" data-fg=\"#000\">The only real valuable thing is intuition.</aside>
    ", Blog::find(1));

    dd($json);

});