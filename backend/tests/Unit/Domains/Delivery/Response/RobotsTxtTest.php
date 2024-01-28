<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Route\PermalinkRepository;

it('returns robots.txt', function () {

    $blog = blogWithLanguage();

    $pathMatcher = new PathMatcher($blog, '/robots.txt');
    $responseObject = $pathMatcher->getResponseObject();

    $blogUrl = PermalinkRepository::getBlogPermalink($blog, $blog->languages[0]);

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->mime_type)->toBe('text/plain');
    expect($responseObject->status)->toBe(200);
    expect($responseObject->content)->toBe("User-agent: *
Sitemap: $blogUrl/sitemap.xml
Disallow: /p/");
});
