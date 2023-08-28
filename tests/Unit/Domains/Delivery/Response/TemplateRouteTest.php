<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('matches custom route and template', function () {

    $content = 'I am a custom template-based route';

    $blog = blogWithLanguage();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'route-test.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($blog, '/test');
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);

    expect($responseObject->mime_type)->toBe('text/html');
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);
});

it('sets custom mime type', function() {

    $blog = blogWithLanguage();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'route-test.json.twig',
        '{}',
    );

    $pathMatcher = new PathMatcher($blog, '/test.json');
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->content)->toBe('{}');
    expect($responseObject->mime_type)->toBe('application/json');
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);

});
