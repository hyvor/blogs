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

});