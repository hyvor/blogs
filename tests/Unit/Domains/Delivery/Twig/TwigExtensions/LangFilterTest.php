<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Language\LanguageRepository;
use App\Domains\Theme\ThemeFilesRepository;

test('lang', function () {
    $blogObject = getBlogObject();
    $blog = BlogService::getBlogBySubdomain($blogObject->subdomain);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'en.yaml',
        'test: Test'
    );

    testTwigRendering(
        "{{ 'test' | lang }}",
        [
            '_blog' => $blogObject,
            '_lang' => ['code' => 'en']
        ],
        'Test'
    );
});

test('lang with single placeholder', function () {
    $blogObject = getBlogObject();
    $blog = BlogService::getBlogBySubdomain($blogObject->subdomain);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'en.yaml',
        'test: "* authors"'
    );

    testTwigRendering(
        "{{ 'test' | lang(2) }}",
        [
            '_blog' => $blogObject,
            '_lang' => ['code' => 'en']
        ],
        '2 authors'
    );
});

test('lang with multiple placeholders', function () {
    $blogObject = getBlogObject();
    $blog = BlogService::getBlogBySubdomain($blogObject->subdomain);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'en.yaml',
        'test: "Written by {name} {date}"'
    );

    testTwigRendering(
        "{{ 'test' | lang(name='hyvor', date='today') }}",
        [
            '_blog' => $blogObject,
            '_lang' => ['code' => 'en']
        ],
        'Written by hyvor today'
    );
});

it('fallbacks to default language', function () {
    $blogObject = getBlogObject();
    $blog = BlogService::getBlogBySubdomain($blogObject->subdomain);

    LanguageRepository::createLanguage(
        $blog,
        'fr',
        'French'
    );

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'en.yaml',
        "test1: 'english-test'\ntest2: english"
    );

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'fr.yaml',
        'test2: french'
    );


    testTwigRendering(
        "{{ 'test1' | lang }}{{ 'test2' | lang }}",
        [
            '_blog' => $blogObject,
            '_lang' => ['code' => 'fr']
        ],
        'english-testfrench'
    );
});
