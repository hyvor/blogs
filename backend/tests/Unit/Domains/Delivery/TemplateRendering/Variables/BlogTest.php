<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _blog variable', function () {
    $content = '{{ _blog.subdomain }}';

    $blog = blogWithLanguageAndRoutes();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe($blog->subdomain);
});
