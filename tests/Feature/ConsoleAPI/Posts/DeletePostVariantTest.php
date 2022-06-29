<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostVariantDeletedEvent;
use Illuminate\Support\Facades\Event;

it('deletes a post variant', function () {
    Event::fake();

    $post = $this->blog->posts()->first();

    $variants = $post->variants()->count();

    $this
        ->callConsoleApi('DELETE', "/post/$post->id/variant", [
            'language_id' => $this->blog->languages[1]->id,
        ])
        ->assertOk();

    $this->assertEquals($variants - 1, $post->variants()->count());

    Event::assertDispatched(PostVariantDeletedEvent::class);
});

it('does not delete primary language variant', function () {
    $post = $this->blog->posts()->first();

    $this
        ->callConsoleApi('DELETE', "/post/$post->id/variant", [
            'language_id' => $this->blog->languages[0]->id,
        ])
        ->assertUnprocessable();
});
