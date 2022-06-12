<?php

namespace Tests\Unit\Domains\Delivery\Response;

use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Theme\ThemeFilesRepository;

it('matches tag page', function () {
    $content = 'I am a tag';

    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'tag.twig',
        $content,
    );

    $tag = $this->blog->tags[0];

    $pathMatcher = new PathMatcher($this->blog, "/tag/$tag->slug");
    $responseObject = $pathMatcher->getResponseObject();

    $this->assertEquals(DeliveryAPITypeEnum::FILE, $responseObject->type);
    $this->assertEquals($content, $responseObject->content);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::TEMPLATE);

});