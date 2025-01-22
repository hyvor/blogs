<?php

namespace Tests\Feature\DataAPI;

it('searches posts in English', function () {

    $blog = blog();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    $post = addPost($blog, [], [
        'title' => 'How to make a cake',
        'status' => 'published',
        'ts_language' => 'english'
    ]);

    $post = addPost($blog, [], [
        'title' => 'How to make a pie',
        'status' => 'published',
        'ts_language' => 'english'
    ]);

    // cake
    dataApi($blog, '/posts/search', [
            'search' => 'cake',
        ])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'How to make a cake');

    // stemming
    dataApi($blog, '/posts/search', [
        'search' => 'making',
    ])
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.title', 'How to make a cake')
        ->assertJsonPath('data.1.title', 'How to make a pie');
});

it('searches in french - also searches description and content', function() {

    $blog = blog();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    $post1 = addPost($blog, [], [
        'description' => 'Comment faire un gâteau',
        'status' => 'published',
        'ts_language' => 'french'
    ]);

    $post2 = addPost($blog, [], [
        'content_text' => 'Étape 1: mélanger les ingrédients',
        'status' => 'published',
        'ts_language' => 'french'
    ]);

    dataApi($blog, '/posts/search', [
        'search' => 'étape',
    ])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $post2->id);

});

it('does not work without search query', function () {

    $blog = blog();
    dataApi($blog, '/posts/search')->assertUnprocessable();

});
