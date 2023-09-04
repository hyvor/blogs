<?php

namespace Tests\Feature\ConsoleAPI\LinkAnalysis;

use App\Data\Enums\PostStatusEnum;
use Illuminate\Support\Facades\Http;

it('does not allow updating posts of other blogs', function() {

    $blog = blogWithAccess();

    $otherBlog = blogWithLanguage();
    $post = addPost($otherBlog, [], [
        'status' => PostStatusEnum::PUBLISHED,
    ]);

    consoleApi($blog, 'POST', '/link-analysis/check-urls', [
        'post_variant_id' => $post->variants()->first()->id,
        'urls' => [],
    ])
        ->assertUnprocessable()
        ->assertSee('Post variant does not belong to this blog');

});

it('checks post variant links', function() {

    Http::fake([
        'https://hyvor.com' => Http::response('', 200),
        'https://endpoint.com' => Http::response('', 404),
    ]);

    $blog = blogWithAccessLanguageAndRoutes();

    $post = addPost($blog, [], [
        'status' => PostStatusEnum::PUBLISHED,
    ]);
    $postVariant = $post->variants()->first();

    consoleApi($blog, 'POST', '/link-analysis/check-urls', [
        'post_variant_id' => $postVariant->id,
        'urls' => [
            'https://hyvor.com',
            'https://endpoint.com'
        ],
    ])
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.url', 'https://hyvor.com')
        ->assertJsonPath('0.status_code', 200)
        ->assertJsonPath('1.url', 'https://endpoint.com')
        ->assertJsonPath('1.status_code', 404);

    $postVariant->refresh();

    expect($postVariant->link_analysis)->toBe([
        'https://hyvor.com' => 200,
        'https://endpoint.com' => 404
    ]);

});

it('checks with relative URL', function() {

    $blog = blogWithAccessLanguageAndRoutes([
        'subdomain' => 'my-subdomain'
    ]);
    $post = addPost($blog, [], [
        'status' => PostStatusEnum::PUBLISHED,
        'slug' => 'post-slug'
    ]);
    $postVariant = $post->variants()->first();

    $fullUrl = "https://my-subdomain.hyvorblogs.io/post-slug";

    Http::fake([
        $fullUrl => Http::response('', 200),
    ]);

    consoleApi($blog, 'POST', '/link-analysis/check-urls', [
        'post_variant_id' => $postVariant->id,
        'urls' => [
            'post-slug'
        ],
    ])
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.url', 'post-slug')
        ->assertJsonPath('0.full_url', $fullUrl)
        ->assertJsonPath('0.status_code', 200);

    $postVariant->refresh();

    expect($postVariant->link_analysis)->toBe([
        'post-slug' => 200,
    ]);

});