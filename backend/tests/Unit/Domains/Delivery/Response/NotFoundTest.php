<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('should return 404', function () {

    $blog = blogWithLanguageAndRoutes();

    $pathMatcher = new PathMatcher($blog, "/not-found");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('404');

});

it('should return 404.twig rendered', function() {

    $blog = blogWithLanguageAndRoutes();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        '404.twig',
        '404 not found. {{ _blog.subdomain }}'
    );

    $pathMatcher = new PathMatcher($blog, "/not-found");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('404 not found. ' . $blog->subdomain);

});