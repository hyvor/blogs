<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Route\RouteRepository;
use App\Domains\Theme\ThemeFilesRepository;

it('matches custom route and template', function () {

    RouteRepository::createRoute(
        $this->blog,
        'test',
        '/test',
        'test',
    );

    $content = 'I am a custom route';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'test.twig',
        $content,
    );

    $pathMatcher = new PathMatcher($this->blog, "/test");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);

    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);
});