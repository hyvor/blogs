<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('matches index page', function () {
    $content = 'Hello World';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, '/');
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);
});

