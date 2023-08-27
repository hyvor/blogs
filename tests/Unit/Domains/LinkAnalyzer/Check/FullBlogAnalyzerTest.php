<?php

namespace Tests\Unit\Domains\LinkAnalyzer;

use App\Data\Enums\PostStatusEnum;
use App\Domains\LinkAnalyzer\Check\FullBlogAnalyzer;
use App\Models\LinkAnalyzerLink;
use Illuminate\Support\Facades\Http;
use Tests\Helper\Generator\PostContentGenerator;

it('analyzes a blog', function() {

    $blog = blogWithLanguage();

    Http::fake([
        'hyvor.com/*' => Http::response('', 200),
        'example.com/*' => Http::response('', 301),
        '*.hyvorblogs.io/*' => Http::response('', 200),
        'https://broken.com/*' => Http::response('', 404),
    ]);

    $post1 = addPost($blog, [], [
        'status' => PostStatusEnum::PUBLISHED,
        'content' => PostContentGenerator::generateWithLinks([
            'https://hyvor.com/about',
            'https://example.com/1',
            'ftp://example.com/2',
            '/about'
        ])
    ]);

    $post2 = addPost($blog, [], [
        'status' => PostStatusEnum::PUBLISHED,
        'content' => PostContentGenerator::generateWithLinks([
            'https://hyvor.com/pricing',
            'https://broken.com/1',
        ])
    ]);

    $post3 = addPost($blog, [], ['status' => PostStatusEnum::DRAFT]);

    $analyze = new FullBlogAnalyzer($blog);
    $analyze->analyze();


    expect($analyze->postsCount)->toBe(3);
    expect($analyze->postVariantsCount)->toBe(2);

    expect($analyze->linksCount)->toBe(5);
    expect($analyze->linksOkCount)->toBe(3);
    expect($analyze->linksBrokenCount)->toBe(1);
    expect($analyze->linksRedirectCount)->toBe(1);

    $links = LinkAnalyzerLink::get();
    expect($links->count())->toBe(5);

    $post1Links = $links->filter(fn($link) => $link->post_variant_id === $post1->variants[0]->id);

    $post1Link1 = $post1Links[0];
    expect($post1Link1->url)->toBe('https://hyvor.com/about');
    expect($post1Link1->status_code)->toBe(200);

    $post1Link2 = $post1Links[1];
    expect($post1Link2->url)->toBe('https://example.com/1');
    expect($post1Link2->status_code)->toBe(301);

    $post1Link3 = $post1Links[2];
    expect($post1Link3->url)->toContain('hyvorblogs.io/about');
    expect($post1Link3->status_code)->toBe(200);

    $post2Links = $links
        ->filter(fn($link) => $link->post_variant_id === $post2->variants[0]->id)
        ->values();

    $post2Link1 = $post2Links[0];
    expect($post2Link1->url)->toBe('https://hyvor.com/pricing');
    expect($post2Link1->status_code)->toBe(200);

    $post2Link2 = $post2Links[1];
    expect($post2Link2->url)->toBe('https://broken.com/1');
    expect($post2Link2->status_code)->toBe(404);

});

it('it clears old links but keeps ignored links as ignored', function() {

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


    $links =LinkAnalyzerLink::factory()->count(5)->create([
        'blog_id' => $blog->id,
        'post_variant_id' => $post->variants[0]->id,
    ]);
    $links[0]->update([
        'url' => 'https://hyvor.com/about',
        'ignore' => true
    ]);

    $analyze = new FullBlogAnalyzer($blog);
    $analyze->analyze();

    expect($analyze->linksIgnoredCount)->toBe(1);

    expect(LinkAnalyzerLink::count())->toBe(2);

    $links = LinkAnalyzerLink::get();
    expect($links[0]->url)->toBe('https://hyvor.com/about');
    expect($links[0]->ignore)->toBe(true);
    expect($links[0]->status_code)->toBe(200);

    expect($links[1]->url)->toBe('https://example.com/1');
    expect($links[1]->ignore)->toBe(false);
    expect($links[1]->status_code)->toBe(301);

});