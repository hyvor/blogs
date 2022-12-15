<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _lang variable', function () {
    $content = '{{ _lang.code }}';

    $blog = blogWithLanguageAndRoutes();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe($blog->languages[0]->code);
});

it('sets _lang variable for another language', function () {
    $content = '{{ _lang.code }}';

    $blog = blogWithLanguageAndRoutes();
    $language = addLanguage($blog);
    $blog->refresh();
    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/' . $language->code);
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe($language->code);

});
