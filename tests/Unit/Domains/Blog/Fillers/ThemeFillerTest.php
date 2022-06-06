<?php

namespace Tests\Unit\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\Fillers\ThemeFiller;
use App\Domains\Theme\GithubSync\GithubSyncService;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\Theme\ThemeRepository;
use App\Models\ThemeFile;

beforeEach(function() {

    $zip = file_get_contents(test_unit_data_path('Themes/github-themes.zip'));
    GithubSyncService::sync($zip);

});

it('copies the default theme', function() {


    $blog = newBlog();

    $themeFiller = new ThemeFiller($blog);
    $themeFiller->fill();

    expect(ThemeFilesRepository::getFile($blog, 'config.yaml'))->toBeInstanceOf(ThemeFile::class);
    expect(ThemeFilesRepository::getFile($blog, 'index.twig', ThemeFileFolderEnum::TEMPLATES))
        ->toBeInstanceOf(ThemeFile::class);

    expect($blog->theme_version_id)
        ->toBe(
            ThemeRepository::getThemeLatestVersion(
                ThemeRepository::getThemeByName('default')
            )->id
        );

});

it('copies the blank theme for DEV blogs', function() {

    $blog = newBlog(BlogTypeEnum::DEV);

    $themeFiller = new ThemeFiller($blog);
    $themeFiller->fill();

    expect(ThemeFilesRepository::getFile($blog, 'config.yaml'))->toBeInstanceOf(ThemeFile::class);
    expect(ThemeFilesRepository::getFile($blog, 'index.twig', ThemeFileFolderEnum::TEMPLATES))
        ->toBeInstanceOf(ThemeFile::class);

    expect($blog->theme_version_id)
        ->toBe(
            ThemeRepository::getThemeLatestVersion(
                ThemeRepository::getThemeByName('blank')
            )->id
        );

});

it('does not do anything for preview blogs', function() {

    $blog = newBlog(BlogTypeEnum::PREVIEW);

    $themeFiller = new ThemeFiller($blog);
    $themeFiller->fill();

    expect(ThemeFile::where('blog_id', $blog->id)->count())->toBe(0);

});