<?php

namespace Tests\Feature\DeliveryApi\PathMatcher;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('matches assets', function () {
    $file = 'script.js';
    $content = 'var x = null';

    $blog = blog();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::ASSETS,
        $file,
        $content,
    );

    $pathMatcher = new PathMatcher($blog, "/assets/$file");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::ASSET);
});

it('matches default assets', function () {
    $file = 'flashload.js';
    $blog = blog();

    $pathMatcher = new PathMatcher($blog, "/assets/$file");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::ASSET);
    expect($responseObject->content)->toBeString();
});

it('does not match if asset is not found', function () {
    $file = 'missing.js';

    $blog = blogWithLanguage();

    $pathMatcher = new PathMatcher($blog, "/assets/$file");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->status)->toBe(404);
});
