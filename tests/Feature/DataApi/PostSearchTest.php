<?php

namespace Tests\Feature\DataAPI;

/**
 * Testing search is not easy
 * Seeding meilisearch is asynchronous
 * The Collection driver does not support ->where()
 * So, only statuses are checked
 */
beforeEach(function () {

    $this->blog = blog();
    addPrimaryLanguage($this->blog);

    $post = addPost($this->blog);
    $variants = $post->variants;
    $variants[0]->update(['title' => 'English', 'status' => 'published']);

});

it('searches posts', function () {
    dataApi($this->blog, '/posts/search', [
            'search' => 'English',
        ])
        ->assertOk();
})->skip();

it('does not work without search query', function () {

    dataApi('/posts/search')->assertUnprocessable();

})->skip();
