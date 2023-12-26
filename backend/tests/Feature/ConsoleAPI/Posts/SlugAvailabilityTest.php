<?php

namespace Tests\Feature\ConsoleAPI\Posts;

it('checks for slug availability', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    $post = addPost($blog);

    $slug = 'hello-world';

    consoleApi($blog, 'GET', "/post/$post->id/slug-available", [
        'slug' => $slug,
        'language_id' => $blog->languages[0]->id
    ])
        ->assertOk()
        ->assertJsonPath('available', true);

});

it('when slug is taken', function() {

    $slug = 'hello-world';

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    $post = addPost($blog);
    $post->variants[0]->update(['slug' => $slug]);

    $post2 = addPost($blog);

    consoleApi($blog, 'GET', "/post/$post2->id/slug-available", [
        'slug' => $slug,
        'language_id' => $blog->languages[0]->id
    ])
        ->assertOk()
        ->assertJsonPath('available', false);

});

it('when slug is taken by a variant of another language', function() {

    $slug = 'hello-world';

    $blog = blogWithAccess();
    $primaryLang = addPrimaryLanguage($blog);
    $secondLang = addLanguage($blog);

    $post = addPost($blog);
    $post->variants->where('language_id', $primaryLang->id)->first()->update(['slug' => $slug]);

    $post2 = addPost($blog);

    consoleApi($blog, 'GET', "/post/$post2->id/slug-available", [
        'slug' => $slug,
        'language_id' => $secondLang->id
    ])
        ->assertOk()
        ->assertJsonPath('available', true);

});