<?php
namespace Tests\Feature\ConsoleAPI\LinkAnalysis;

use App\Models\LinkAnalyzerLink;

it('ignores link', function($current) {

    $blog = blogWithAccessLanguageAndRoutes();
    $post = addPublishedPost($blog);
    $postVariant = $post->variants[0];

    $url = 'https://hyvor.com';

    $link = LinkAnalyzerLink::factory()->create([
        'blog_id' => $blog->id,
        'post_variant_id' => $postVariant->id,
        'url' => $url,
        'ignore' => $current
    ]);

    consoleApi($blog, 'PATCH', '/link-analysis/ignore-link', [
        'post_variant_id' => $postVariant->id,
        'url' => $url,
        'status' => !$current
    ])
        ->assertOk();

    $link->refresh();
    expect($link->ignore)->toBe(!$current);

    expect($postVariant->refresh()->link_analysis)->toBe([
        $url => $current ? $link->status_code : -2
    ]);

})->with([true, false]);