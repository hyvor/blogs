<?php

namespace Tests\Unit\Domains\Delivery\TemplateRendering\Variables;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('sets _blog variable', function() {

    $content = '{{ _blog.subdomain }}';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('test');

});