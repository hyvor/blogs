<?php

namespace Tests\Feature\DataAPI;

/**
 * Testing search is not easy
 * Seeding meilisearch is asynchronous
 * The Collection driver does not support ->where()
 * So, only statuses are checked
 */
beforeEach(function () {
    $post = blog()->posts()->where('is_page', false)->first();
    $variants = $post->variants;

    $variants[0]->update(['title' => 'English', 'status' => 'published']);
    $variants[1]->update(['title' => 'French', 'status' => 'published']);
});

it('searches posts', function () {
    $this
        ->callDataApi('/posts/search', [
            'search' => 'English',
        ])
        ->assertOk();
});

it('does not work without search query', function () {
    $this
        ->callDataApi('/posts/search')
        ->assertUnprocessable();
});
