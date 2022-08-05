<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;

it('returns the file contents', function() {

    $blogObject = getBlogObject();

    ThemeFilesRepository::createOrUpdateFile(
        Blog::where('subdomain', $blogObject->subdomain)->first(),
        ThemeFileFolderEnum::ASSETS,
        'test.txt',
        'test'
    );

    testTwigRendering(
        "{{ 'test.txt' | asset }}",
        [
            '_blog' => $blogObject,
        ],
        'test'
    );

});

it('returns empty string when the file is not found', function() {

    $blogObject = getBlogObject();

    testTwigRendering(
        "{{ 'test.txt' | asset }}",
        [
            '_blog' => $blogObject,
        ],
        ''
    );

});