<?php
namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _lang variable', function() {

    $content = '{{ _lang.code }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('en');

});

it('sets _lang variable for another language', function() {

    $content = '{{ _lang.code }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/fr');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('fr');

});