<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Models\Post;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets a post', function() {

    $post = Post::where('blog_id', config('test.blog_id'))->first();

    $this
        ->callConsoleApi('GET', "/post/$post->id")
        ->assertOk()
        ->assertJson(function(AssertableJson $json) use ($post) {
            $json->where('id', $post->id)
                ->etc();
        });

});

it('forbids getting posts of other blogs', function() {

    $post = Post::where('blog_id', config('test.not_blog_id'))->first();

    $this->callConsoleApi('GET', "/post/$post->id")->assertForbidden();

});

it('returns 404 on missing posts', function() {
    $this->callConsoleApi('GET', "/post/1203912903091")->assertNotFound();
});