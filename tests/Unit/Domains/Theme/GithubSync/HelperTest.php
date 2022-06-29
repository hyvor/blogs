<?php

namespace Tests\Unit\Domains\Themes\GithubSync;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\GithubSync\Helper;
use App\Domains\Theme\GithubSync\Theme;
use App\Models\Theme as ThemeModel;
use App\Models\ThemeVersion;
use PhpZip\ZipFile;

it('gets latest versions of themes', function () {
    $themes = ThemeModel::factory()
        ->count(3)
        ->has(ThemeVersion::factory()->count(4), 'versions')
        ->create();

    $latestVersions = Helper::getLatestVersionsOfAllThemes();

    expect(count($latestVersions))->toBe($themes->count());
});

it('generates zip', function () {
    $theme = new Theme('default', ThemeCreationTypeEnum::ORIGINAL);
    $theme->addFile(null, 'config.yaml', 'THEME_NAME: default');
    $theme->addFile(ThemeFileFolderEnum::TEMPLATES, 'index.twig', 'index.twig');

    $zipString = Helper::generateZip($theme);

    $zip = new ZipFile();
    $zip->openFromString($zipString);

    expect($zip['config.yaml'])->toBe('THEME_NAME: default');
    expect($zip['templates/index.twig'])->toBe('index.twig');
});
