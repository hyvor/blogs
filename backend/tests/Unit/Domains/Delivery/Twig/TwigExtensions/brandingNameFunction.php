<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\BlogService;
use App\Domains\Theme\ThemeFilesRepository;

dataset('brandingNames', [
    ['Test-Placeholder', 'Test-Placeholder'],
    ['', 'Powered by Hyvor Blogs'],
    [' ', 'Powered by Hyvor Blogs'],
]);

test('branding_name', function ($placeholder, $expected) {
    $blogObject = getBlogObject();
    $blog = BlogService::getBlogBySubdomain($blogObject->subdomain);

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::LANG,
        'en.yaml',
        'hb_branding: ' . $placeholder
    );

    testTwigRendering(
        "{{ branding_name() }}",
        [
            '_blog' => $blogObject,
            '_lang' => ['code' => $blog->languages[0]->code]
        ],
        $expected
    );
})->with('brandingNames');