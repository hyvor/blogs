<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Models\Post;
use App\Models\PostVariant;
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
            fn (AssertableJson $json) => $json
                ->where('status', $status)
                ->where('content', $content)
                ->where('content_unsaved', $contentUnsaved)
                ->where('title', $title)
                ->where('description', $description)
                ->etc()
        );

    Event::assertDispatched(PostVariantUpdatedEvent::class);
});

it('updates post published_at when post status is changed to published', function () {
    $blog = blog();
    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
    ]);
    PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
    ]);

    $this
        ->callConsoleApi('PATCH', "/post/$post->id/variant", [
            'language_id' => $blog->languages[0]->id,
            'status' => 'published',
        ])
        ->assertOk();

    expect($post->refresh()->published_at)->not->toBeNull();
});

it('sets the slug if it is empty when publishing the primary language post', function() {


    $blog = blog();
    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
    ]);
    PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
    ]);

    $this
        ->callConsoleApi('PATCH', "/post/$post->id/variant", [
            'language_id' => $blog->languages[0]->id,
            'status' => 'published',
        ])
        ->assertOk();

    expect($post->refresh()->slug)->not->toBeNull();

});