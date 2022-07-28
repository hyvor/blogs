<?php

namespace Tests\Unit\Domains\Delivery\Response\Sitemap;

use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Domains\Delivery\PathMatcher;
use Symfony\Component\DomCrawler\Crawler;

it('does not work for page 0', function() {

    $pathMatcher = new PathMatcher(blog(), '/sitemap-posts-0.xml');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->status)->toBe(404);

});

it('generates posts sitemap', function() {

    $blog = newBlog();
    (new LanguageFiller($blog))->fill();
    (new RouteFiller($blog))->fill();
    seedPublishedPosts(5, $blog);

    $pathMatcher = new PathMatcher($blog, '/sitemap-posts-1.xml');
    $responseObject = $pathMatcher->getResponseObject();

    $doc = $responseObject->content;
    $crawler = new Crawler($doc);

    expect($crawler->filter('default|url')->count())->toBe(5);
    expect($crawler->filter('default|loc')->count())->toBe(5);

});

it('paginates', function() {

    config(['limits.max_entries_per_sitemap' => 2]);

    $blog = newBlog();
    (new LanguageFiller($blog))->fill();
    (new RouteFiller($blog))->fill();
    seedPublishedPosts(3, $blog);

    $crawler1 = new Crawler((new PathMatcher($blog, '/sitemap-posts-1.xml'))->getResponseObject()->content);
    expect($crawler1->filter('default|url')->count())->toBe(2);

    $crawler2 = new Crawler((new PathMatcher($blog, '/sitemap-posts-2.xml'))->getResponseObject()->content);
    expect($crawler2->filter('default|url')->count())->toBe(1);

    expect((new PathMatcher($blog, '/sitemap-posts-3.xml'))->getResponseObject()->status)->toBe(404);

});