<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostVariantUpdatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates post variant', function () {
    Event::fake();

    $post = $this->blog->posts()->first();
    $variant = $post->variants[0];
    $language = $variant->language;

    $status = 'scheduled';
    $content = 'this is content';
    $contentUnsaved = 'this is unsaved content';
    $title = 'this is a title';
    $description = 'a description';

    $this
        ->callConsoleApi('PATCH', "/post/$post->id/variant", [
            'language_id' => $language->id,
            'status' => $status,
            'content' => $content,
            'content_unsaved' => $contentUnsaved,
            'title' => $title,
            'description' => $description,
        ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json
                ->where('status', $status)
                ->where('content', $content)
                ->where('content_unsaved', $contentUnsaved)
                ->where('title', $title)
                ->where('description', $description)
                ->etc()
        );

    Event::assertDispatched(PostVariantUpdatedEvent::class);
});
