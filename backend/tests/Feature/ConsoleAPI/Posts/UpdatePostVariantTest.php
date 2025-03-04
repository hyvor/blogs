<?php

declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Language\LanguageRepository;
use App\Domains\Post\Events\PostVariantUpdatedEvent;
use App\Domains\Redirect\RedirectRepository;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\PostVariantHistory;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Helper\Generator\PostContentGenerator;

it('updates post variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);
    $post = addPost($blog);
    $variant = $post->variants[0];
    $language = $variant->language;

    $slug = 'hello-world';
    $status = 'scheduled';
    $content = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"this is content"}]}]}';
    $contentUnsaved = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"this is content unsaved"}]}]}';
    $title = 'this is a title';
    $description = 'a description';

    $primaryKeyword = 'primary keyword';
    $secondaryKeywords = ['secondary keyword 1', 'secondary keyword 2'];

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $language->id,
        'slug' => $slug,
        'status' => $status,
        'content' => $content,
        'content_unsaved' => $contentUnsaved,
        'title' => $title,
        'description' => $description,

        'seo_primary_keyword' => $primaryKeyword,
        'seo_secondary_keywords' => $secondaryKeywords,
    ])
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json) => $json
                ->where('slug', $slug)
                ->where('status', $status)
                ->where('content', $content)
                ->where('content_unsaved', $contentUnsaved)
                ->where('title', $title)
                ->where('description', $description)
                ->where('seo_primary_keyword', $primaryKeyword)
                ->where('seo_secondary_keywords', $secondaryKeywords)
                ->etc()
        );

    Event::assertDispatched(PostVariantUpdatedEvent::class);

    expect(RedirectRepository::hasRedirectForPath($blog, $slug))->toBeFalse();
});

it('prevents updating content when prosemirror doc is invalid', function () {
    $blog = blogWithAccessLanguageAndRoutes();
    $post = addPost($blog);
    $variant = $post->variants[0];
    $language = $variant->language;

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $language->id,
        'content' => '<p>invalid html</p>',
    ])
        ->assertUnprocessable()
        ->assertSee('Unable to decode JSON');
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


// bug#134
it('does not update published_at if it is already set', function () {
    $blog = blogWithAccessLanguageAndRoutes();

    $publishedAt = now()->subDay();

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => $publishedAt,
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

    expect($post->refresh()->published_at->getTimestamp())->toBe($publishedAt->getTimestamp());
});

it('sets the slug if it is empty when publishing the primary language post', function () {
    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
    ]);
    $variant = PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
    ]);

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'status' => 'published',
    ])
        ->assertOk();

    expect($variant->refresh()->slug)->not->toBeNull();
});

// bug#89
it('updates slug when title is empty', function () {
    $blog = blogWithAccessLanguageAndRoutes();

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
        // 'slug' => null
    ]);
    $variant = PostVariant::factory()->create([
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

    expect($variant->refresh()->slug)->not->toBeNull();
});


it('checks for duplicates when generating the slug from the title', function () {
    $blog = blogWithAccessLanguageAndRoutes();

    addPublishedPost($blog, [], [
        'slug' => 'my-post'
    ]);

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
    ]);
    $variant = PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
        'slug' => null,
        'title' => 'My Post',
    ]);

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'status' => 'published'
    ])
        ->assertOk();

    expect($variant->refresh()->slug)->not->toBe('my-post-1');
    expect($variant->refresh()->slug)->toBeString();
});

it('creates a history if post content has changed', function () {
    $blog = blogWithAccessLanguageAndRoutes();

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
    ]);
    $variant = PostVariant::factory()->create([
        'post_id' => $post,
        'language_id' => $blog->languages[0],
        'status' => PostStatusEnum::DRAFT,
    ]);

    $para = PostContentGenerator::generateParagraph('Test content');

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'content' => $para
    ])
        ->assertOk();

    expect($variant->history()->first()->content)->toBe($para);
});

it('does not update if the content is the same', function () {
    $blog = blogWithAccessLanguageAndRoutes();
    $para = PostContentGenerator::generateParagraph('Test content');

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
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


it('deletes old histories', function () {
    $blog = blogWithAccessLanguageAndRoutes();

    $post = Post::factory()->create([
        'blog_id' => $blog,
        'published_at' => null,
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

    $para = PostContentGenerator::generateParagraph('Test content');

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'content' => $para
    ])
        ->assertOk();

    expect($variant->history()->orderBy('id', 'desc')->first()->content)->toBe($para);

    expect(PostVariantHistory::count())->toBe(25);
});

it('does not allow slashes in the slug', function () {
    $blog = blogWithAccessLanguageAndRoutes();
    $post = addPost($blog, [], [
        'slug' => 'hello-world'
    ]);

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $blog->languages[0]->id,
        'slug' => 'hello/world',
    ])
        ->assertStatus(422)
        ->assertSee('Slug cannot contain \/');
});

it('checks for duplicates when updating slug', function () {
    $blog = blogWithAccessLanguageAndRoutes();
    $language1 = addLanguage($blog);
    $post = addPost($blog, [], [
        'language_id' => $language1->id,
        'slug' => 'hello-world'
    ]);

    $post2 = addPost($blog, [], [
        'language_id' => $language1->id,
    ]);

    consoleApi($blog, 'PATCH', "/post/$post2->id/variant", [
        'language_id' => $language1->id,
        'slug' => 'hello-world',
    ])
        ->assertStatus(422)
        ->assertSee('Slug has already been taken');
});

it('updates to null', function () {
    $blog = blogWithAccessLanguageAndRoutes();
    $language1 = LanguageRepository::getPrimaryLanguage($blog);
    $post = addPost($blog, [], [
        'language_id' => $language1->id,
        'title' => 'Title',
        'description' => 'test',
        'seo_primary_keyword' => 'keyword'
    ]);

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $language1->id,
        'title' => null,
        'description' => null,
        'seo_primary_keyword' => null,
    ])
        ->assertOk()
        ->assertJsonPath('title', null)
        ->assertJsonPath('description', null)
        ->assertJsonPath('seo_primary_keyword', null);
});

it('adds a redirect automatically', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);
    $post = addPost($blog);
    $variant = $post->variants[0];
    $language = $variant->language;

    $slug = 'newSlug';

    consoleApi($blog, 'PATCH', "/post/$post->id/variant", [
        'language_id' => $language->id,
        'slug' => $slug,
        'auto_redirects' => true,
    ])
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json) => $json
                ->where('slug', $slug)
                ->etc()
        );

    $redirect = RedirectRepository::getRedirects($blog, $variant->slug, 1)->first();
    expect($redirect->to)->toBe($slug);
});
