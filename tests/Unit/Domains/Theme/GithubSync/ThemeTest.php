<?php

namespace Tests\Unit\Domains\Themes\GithubSync;

use App\Data\Enums\ThemeCreationTypeEnum;
use App\Domains\Theme\GithubSync\Theme;
use Exception;

it('gets version', function () {
    $theme = new Theme('default', ThemeCreationTypeEnum::ORIGINAL);
    $theme->addFile(null, 'config.yaml', 'THEME_VERSION: 1.1.0');

    $version = $theme->getVersion();

    expect($version)->toBe('1.1.0');
});

it('throws an error if config.yaml is not found', function () {
    $theme = new Theme('default', ThemeCreationTypeEnum::ORIGINAL);
    $theme->getVersion();
})->throws(Exception::class);


it('throws an error if THEME_VERSION is not found', function () {
    $theme = new Theme('default', ThemeCreationTypeEnum::ORIGINAL);
    $theme->addFile(null, 'config.yaml', 'THEME_NAME: default');
    $theme->getVersion();
})->throws(Exception::class);
