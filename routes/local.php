<?php

use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Theme\GithubSyncService;
use App\Models\Blog;
use Illuminate\Support\Facades\Route;


Route::get('/github-sync', function() {
    GithubSyncService::fetchAndUpdate();
});

Route::get('callout', function() {

    $json = PostContentRepository::getJsonFromHtml("
        <aside data-emoji=\"💡\">The only real valuable thing is intuition.</aside>
    ", Blog::find(1));

    dd($json);

});