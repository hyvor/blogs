<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostVariantDeletedEvent;
use Illuminate\Support\Facades\Event;

it('deletes a post variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addLanguage($blog);
    $post = addPost($blog);

    $variants = $post->variants()->count();

    consoleApi($blog, 'DELETE', "/post/$post->id/variant", [
            'language_id' => $blog->languages[1]->id,
        ])
        ->assertOk();

    $this->assertEquals($variants - 1, $post->variants()->count());

    Event::assertDispatched(PostVariantDeletedEvent::class);
});

it('does not delete primary language variant', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    $post = addPost($blog);

    consoleApi($blog, 'DELETE', "/post/$post->id/variant", [
            'language_id' => $blog->languages[0]->id,
        ])
        ->assertUnprocessable();
});
