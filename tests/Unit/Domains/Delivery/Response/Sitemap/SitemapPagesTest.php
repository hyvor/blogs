<?php

namespace Tests\Unit\Domains\Delivery\Response\Sitemap;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Route\PermalinkRepository;
use App\Models\Language;
use App\Models\PostVariant;
use Symfony\Component\DomCrawler\Crawler;

it('generates entries for homepage and its variants', function () {
    $blog = blog();

    // primary
    (new LanguageFiller($blog))->fill();

    // other
    Language::factory()->count(2)->create(['blog_id' => $blog]);

    $pathMatcher = new PathMatcher($blog, '/sitemap-pages.xml');
    $responseObject = $pathMatcher->getResponseObject();

    $doc = $responseObject->content;
    $crawler = new Crawler($doc);

    // only pages sitemap
    expect($crawler->filter('default|loc')->count())->toBe(1);

    $alts = $crawler->filter('default|url > xhtml|link');
    expect($alts->count())->toBe(3);
    expect($alts->eq(0)->attr('hreflang'))->toBe('en');
    expect($alts->eq(0)->attr('href'))->toBe(PermalinkRepository::getFullUrlFromPath($blog));
});

it('generates entries for pages and its variants', function () {
    $blog = blogWithLanguageAndRoutes();

    Language::factory()->count(2)->create(['blog_id' => $blog]);

    $pages = addPosts($blog, 3, ['is_page' => true], ['status' => 'published']); // 3 pages
    addPosts($blog, 1, ['is_page' => false], ['status' => 'published']); // 1 post, to make sure it is not added

    PostVariant::factory()->create(['post_id' => $pages[2]->id, 'status' => 'published']);

    $pathMatcher = new PathMatcher($blog, '/sitemap-pages.xml');
    $responseObject = $pathMatcher->getResponseObject();

    $doc = $responseObject->content;
    $crawler = new Crawler($doc);

    expect($crawler->filter('default|loc')->count())->toBe(4); // index + pages
    expect($crawler->filter('default|loc')->eq(1)->innerText())->toContain($pages[0]->slug);

    // language variants
    expect($crawler->filter('default|url')->eq(2)->filter('xhtml|link')->count())->toBe(1);
    expect($crawler->filter('default|url')->eq(3)->filter('xhtml|link')->count())->toBe(2);
});
