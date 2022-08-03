<?php

namespace Tests\Unit\Domains\Delivery\Response\Sitemap;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Delivery\PathMatcher;
use Symfony\Component\DomCrawler\Crawler;

it('returns sitemap', function () {
    $blog = newBlog();

    (new LanguageFiller($blog))->fill();

    $pathMatcher = new PathMatcher($blog, '/sitemap.xml');
    $responseObject = $pathMatcher->getResponseObject();

    $doc = $responseObject->content;
    $crawler = new Crawler($doc);

    // only pages sitemap
    $sitemaps = $crawler->filter('sitemap');
    expect($sitemaps->count())->toBe(1);
    expect($sitemaps->first()->innerText())->toEndWith('/sitemap-pages.xml');
});

it('adds posts sitemap', function () {
    $blog = newBlog();

    (new LanguageFiller($blog))->fill();

    seedPublishedPosts(5, $blog);

    $pathMatcher = new PathMatcher($blog, '/sitemap.xml');
    $responseObject = $pathMatcher->getResponseObject();

    $doc = $responseObject->content;
    $crawler = new Crawler($doc);

    // only pages sitemap
    $sitemaps = $crawler->filter('sitemap');

    expect($sitemaps->count())->toBe(2);
    expect($sitemaps->eq(0)->innerText())->toEndWith('/sitemap-pages.xml');
    expect($sitemaps->eq(1)->innerText())->toEndWith('/sitemap-posts-1.xml');
});
