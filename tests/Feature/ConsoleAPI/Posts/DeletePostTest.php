<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostDeletedEvent;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Support\Facades\Event;

it('deletes the post and its variants', function () {
    Event::fake();

    $post = Post::where('blog_id', config('test.blog_id'))->first();
    $this->callConsoleApi('DELETE', "/post/$post->id")->assertOk();
    $this->assertNull(Post::find($post->id));
    expect(PostVariant::where('post_id', $post->id)->count())->toBe(0);

    Event::assertDispatched(PostDeletedEvent::class);
});

it('does not delete posts of other blogs', function () {
    $post = Post::where('blog_id', config('test.not_blog_id'))->first();
    $this->callConsoleApi('DELETE', "/post/$post->id")->assertForbidden();
});
