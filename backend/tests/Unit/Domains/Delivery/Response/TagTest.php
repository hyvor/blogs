<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('matches tag page', function () {
    $content = 'I am a tag';

    $blog = blogWithLanguageAndRoutes();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'tag.twig',
        $content,
    );

    $tag = addTag($blog);

    $pathMatcher = new PathMatcher($blog, "/tag/$tag->slug");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);
});
