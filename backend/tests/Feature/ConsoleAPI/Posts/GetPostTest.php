<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Models\Post;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets a post', function () {

    $blog = blogWithAccess();
    $post = addPost($blog);

    consoleApi($blog, 'GET', "/post/$post->id")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($post) {
            $json->where('id', $post->id)
                ->etc();
        });

});

it('forbids getting posts of other blogs', function () {

    $blog = blogWithAccess();
    $post = addPost($blog);

    consoleApi(blogWithAccess(), 'GET', "/post/$post->id")->assertForbidden();
});

it('returns 404 on missing posts', function () {
    consoleApi(blogWithAccess(), 'GET', '/post/1203912903091')->assertNotFound();
});
