<?php

namespace Tests\Unit\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\Theme\ThemeImporter;
use App\Models\ThemeFile;
use PhpZip\ZipFile;

beforeEach(function () {

    // imports the sample-theme

    $zip = new ZipFile();

    $sampleThemePath = base_path('tests/Unit/__DATA__/Themes/sample-theme');
    $zip->addDirRecursive($sampleThemePath);
    $path = storage_path('framework/testing/sample-theme.zip');
    $zip->saveAsFile($path);

    $this->blog = blog();
    $this->importer = new ThemeImporter($this->blog, file_get_contents($path));
});

function getThemeFile(?ThemeFileFolderEnum $folder, string $file)
{
    return ThemeFilesRepository::getFile(blog(), $file, $folder);
}

it('works', function () {
    $this->importer->import();

    // test skipping
    $trace = $this->importer->skipTraces();
    expect(count($trace))->toBe(2);
    expect($trace[0])->toContain('skipped', 'index-in-invalid.twig');
    expect($trace[1])->toContain('invalid.yaml');

    expect(getThemeFile(ThemeFileFolderEnum::ASSETS, 'image.svg'))->toBeInstanceOf(ThemeFile::class);
    expect(getThemeFile(ThemeFileFolderEnum::LANG, 'en.yaml'))->toBeInstanceOf(ThemeFile::class);
    expect(getThemeFile(ThemeFileFolderEnum::TEMPLATES, '@base.twig'))->toBeInstanceOf(ThemeFile::class);
    expect(getThemeFile(ThemeFileFolderEnum::TEMPLATES, 'index.twig'))->toBeInstanceOf(ThemeFile::class);
    expect(getThemeFile(ThemeFileFolderEnum::STYLES, 'index.scss'))->toBeInstanceOf(ThemeFile::class);
    expect(getThemeFile(null, 'config.yaml'))->toBeInstanceOf(ThemeFile::class);

    expect(getThemeFile(null, 'invalid.yaml'))->toBeNull();
    expect(getThemeFile(ThemeFileFolderEnum::ASSETS, 'something-else.svg'))->toBeNull();

    expect(getThemeFile(null, 'config.yaml')->content)->toBe('THEME_NAME=sample');
});

it('fails on invalid input', function () {
    $importer = new ThemeImporter(blog(), 'hi');
    $importer->import();

    expect($importer->success())->toBeFalse();
});
