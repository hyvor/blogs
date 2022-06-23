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
    $content = Str::random();

    $this->callCliAPI('PATCH', '/files', [
        'files' => [
            '/templates/index.twig' => base64_encode($content),
            'config.yaml' => 'name',
        ],
    ])->assertOk();

    $indexTwig = ThemeFilesRepository::getFile(
        $this->blog,
        'index.twig',
        ThemeFileFolderEnum::TEMPLATES
    );

    expect($indexTwig->content)->toBe($content);
});

it('updates files', function () {
    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'Hi'
    );

    $this->callCliAPI('PATCH', "/files", [
        'files' => [
            '/templates/index.twig' => base64_encode('New string'),
        ],
    ])->assertOk();

    $indexTwig = ThemeFilesRepository::getFile(
        $this->blog,
        'index.twig',
        ThemeFileFolderEnum::TEMPLATES
    );

    expect($indexTwig->content)->toBe('New string');
});

it('resets', function () {
    ThemeFilesRepository::createOrUpdateFile(
        $this->blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'Hi'
    );

    $this->callCliAPI('PATCH', "/files", [
        'files' => [],
        'reset' => true,
    ])->assertOk();

    expect(ThemeFile::where('blog_id', $this->blog->id)->count())->toBe(0);
});
