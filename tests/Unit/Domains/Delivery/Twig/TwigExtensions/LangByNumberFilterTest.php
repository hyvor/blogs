<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Theme\ThemeFilesRepository;

it('works for zero', function() {

    $blogObject = getBlogObject();
    $blog = BlogService::getBlogBySubdomain($blogObject->subdomain);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'en.yaml',
        "zero: 'Nothing'\none: One\nmulti: Multiple"
    );

    testTwigRendering(
        "{{ 0 | lang_by_number(zero='zero', one='one', multi='multi') }}",
        [
            '_blog' => $blogObject,
            '_lang' => ['code' => 'en']
        ],
        'Nothing'
    );

});

it('works for one', function() {

    $blogObject = getBlogObject();
    $blog = BlogService::getBlogBySubdomain($blogObject->subdomain);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'en.yaml',
        "zero: 'Nothing'\none: One\nmulti: Multiple"
    );

    testTwigRendering(
        "{{ 1 | lang_by_number(zero='zero', one='one', multi='multi') }}",
        [
            '_blog' => $blogObject,
            '_lang' => ['code' => 'en']
        ],
        'One'
    );

});


it('works for mutli', function() {

    $blogObject = getBlogObject();
    $blog = BlogService::getBlogBySubdomain($blogObject->subdomain);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'en.yaml',
        "zero: 'Nothing'\none: One\nmulti: Multiple"
    );

    testTwigRendering(
        "{{ 100 | lang_by_number(zero='zero', one='one', multi='multi') }}",
        [
            '_blog' => $blogObject,
            '_lang' => ['code' => 'en']
        ],
        'Multiple'
    );

});