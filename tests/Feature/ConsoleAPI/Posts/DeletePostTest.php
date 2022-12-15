<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostDeletedEvent;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Support\Facades\Event;

it('deletes the post and its variants', function () {
    Event::fake();

    $blog = blogWithAccess();

    $post = addPost($blog);
    consoleApi($blog, 'DELETE', "/post/$post->id")->assertOk();
    $this->assertNull(Post::find($post->id));
    expect(PostVariant::where('post_id', $post->id)->count())->toBe(0);

    Event::assertDispatched(PostDeletedEvent::class);
});

it('does not delete posts of other blogs', function () {
    $post = addPost(blog());
    consoleApi(blogWithAccess(), 'DELETE', "/post/$post->id")->assertForbidden();
});
