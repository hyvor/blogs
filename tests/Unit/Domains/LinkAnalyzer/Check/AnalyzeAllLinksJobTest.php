<?php

namespace Tests\Unit\Domains\LinkAnalyzer\Check;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\Check\AnalyzeAllLinksJob;
use App\Models\LinkAnalyzerCheck;
use Illuminate\Support\Facades\Http;
use Tests\Helper\Generator\PostContentGenerator;

it('job works', function() {

    Http::fake([
        'hyvor.com/*' => Http::response('', 200),
        'example.com/*' => Http::response('', 301),
    ]);

    $blog = blogWithLanguage();

    $post = addPost($blog, [], [
        'status' => PostStatusEnum::PUBLISHED,
        'content' => PostContentGenerator::generateWithLinks([
            'https://hyvor.com/about',
            'https://example.com/1',
        ])
    ]);

    $job = new AnalyzeAllLinksJob($blog);
    $job->handle();

    $check = LinkAnalyzerCheck::first();

    expect($check->blog_id)->toBe($blog->id);
    expect($check->posts_count)->toBe(1);
    expect($check->post_variants_count)->toBe(1);
    expect($check->links_total_count)->toBe(2);
    expect($check->links_ok_count)->toBe(1);
    expect($check->links_broken_count)->toBe(0);
    expect($check->links_redirect_count)->toBe(1);

});