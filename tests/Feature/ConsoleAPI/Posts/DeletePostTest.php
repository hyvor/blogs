<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Models\Post;

it('deletes a post', function() {

    $post = Post::where('blog_id', config('test.blog_id'))->first();
    $this->callConsoleApi('DELETE', "/post/$post->id")->assertOk();
    $this->assertNull(Post::find($post->id));

});

it('does not delete posts of other blogs', function() {

    $post = Post::where('blog_id', config('test.not_blog_id'))->first();
    $this->callConsoleApi('DELETE', "/post/$post->id")->assertForbidden();

});