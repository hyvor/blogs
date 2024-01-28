<?php

namespace Tests\Feature\CliAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use App\Models\ThemeFile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->blog = Blog::where('type', BlogTypeEnum::DEV)->first();
});

it('creates files', function () {
    $blog = devBlog();
    $content = Str::random();

    $this->callCliAPI($blog, 'PATCH', '/files', [
        'files' => [
            '/templates/index.twig' => base64_encode($content),
            'config.yaml' => 'name',
        ],
    ])->assertOk();

    $indexTwig = ThemeFilesRepository::getFile(
        $blog,
        'index.twig',
        ThemeFileFolderEnum::TEMPLATES
    );

    expect($indexTwig->content)->toBe($content);
});

it('updates files', function () {

    $blog = devBlog();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'Hi'
    );

    $this->callCliAPI($blog, 'PATCH', '/files', [
        'files' => [
            '/templates/index.twig' => base64_encode('New string'),
        ],
    ])->assertOk();

    $indexTwig = ThemeFilesRepository::getFile(
        $blog,
        'index.twig',
        ThemeFileFolderEnum::TEMPLATES
    );

    expect($indexTwig->content)->toBe('New string');
});

it('resets', function () {

    $blog = devBlog();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'Hi'
    );

    $this->callCliAPI($blog, 'PATCH', '/files', [
        'files' => [],
        'reset' => true,
    ])->assertOk();

    expect(ThemeFile::where('blog_id', $blog->id)->count())->toBe(0);
});
