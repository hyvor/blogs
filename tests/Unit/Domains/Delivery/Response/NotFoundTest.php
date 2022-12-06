<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('should return 404', function () {

    $pathMatcher = new PathMatcher($this->blog, "/not-found");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('404');

});

it('should return 404.twig rendered', function() {

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        '404.twig',
        '404 not found. {{ blog.name }}'
    );

    $pathMatcher = new PathMatcher($this->blog, "/not-found");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('404 not found. ' . $this->blog->name);

});