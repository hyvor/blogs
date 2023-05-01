<?php

namespace Tests\Feature\DataAPI;

use Tests\MeilisearchInefficient;

/**
 * Testing search is not easy
 * Seeding meilisearch is asynchronous
 * The Collection driver does not support ->where()
 * So, only statuses are checked
 */
beforeEach(function () {

    $this->blog = blog();
    addPrimaryLanguage($this->blog);
    addDefaultRoutes($this->blog);

    $post = addPost($this->blog);
    $variants = $post->variants;
    $variants[0]->update(['title' => 'English', 'status' => 'published']);

});

it('searches posts', function () {

    MeilisearchInefficient::waitForAllTasks();

    dataApi($this->blog, '/posts/search', [
            'search' => 'English',
        ])
        ->assertOk()
        ->assertJsonCount(1, 'data');
})->only();

it('does not work without search query', function () {

    dataApi($this->blog, '/posts/search')->assertUnprocessable();

});
