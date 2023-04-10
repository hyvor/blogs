<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\PostVariantHistory;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates post variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);
    $post = addPost($blog);
    $variant = $post->variants[0];
    $language = $variant->language;

    $status = 'scheduled';
    $content = 'this is content';
    $contentUnsaved = 'this is unsaved content';
    $title = 'this is a title';
    $description = 'a description';

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
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

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
    ]);
    PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
    ]);

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
            'language_id' => $blog->languages[0]->id,
            'status' => 'published',
        ])
        ->assertOk();

    expect($post->refresh()->published_at)->not->toBeNull();
});

it('sets the slug if it is empty when publishing the primary language post', function() {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
        'slug' => null
    ]);
    PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
    ]);

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
            'language_id' => $blog->languages[0]->id,
            'status' => 'published',
        ])
        ->assertOk();

    expect($post->refresh()->slug)->not->toBeNull();

});

// bug#89
it('updates slug when title is empty', function() {

    $blog = blogWithAccessLanguageAndRoutes();

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
        'slug' => null
    ]);
    PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
        'title' => null,
    ]);

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'status' => 'published'
    ])
        ->assertOk();

    expect($post->refresh()->slug)->not->toBeNull();

});

it('creates a history if post content has changed', function() {

    $blog = blogWithAccessLanguageAndRoutes();

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
        'slug' => null
    ]);
    $variant = PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
    ]);

    $para = PostContentRepository::generateParagraph('Test content');

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'content' => $para
    ])
        ->assertOk();

    expect($variant->history()->first()->content)->toBe($para);

});

it('does not update if the content is the same', function() {

    $blog = blogWithAccessLanguageAndRoutes();
    $para = PostContentRepository::generateParagraph('Test content');

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
        'slug' => null
    ]);
    $variant = PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
        'content' => $para
    ]);

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'content' => $para
    ])
        ->assertOk();

    expect($variant->history()->count())->toBe(0);

});


it('deletes old histories', function() {

    $blog = blogWithAccessLanguageAndRoutes();

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
        'slug' => null
    ]);
    $variant = PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
    ]);

    PostVariantHistory::factory()
        ->count(25)
        ->create([
            'post_variant_id' => $variant->id
        ]);

    $para = PostContentRepository::generateParagraph('Test content');

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'content' => $para
    ])
        ->assertOk();

    expect($variant->history()->orderBy('id', 'desc')->first()->content)->toBe($para);

    expect(PostVariantHistory::count())->toBe(25);

});