<?php

namespace Tests\Feature\ConsoleAPI\Themes;


use Illuminate\Http\UploadedFile;
use PhpZip\ZipFile;

it('uploads a theme', function () {

    // same as ThemeImporterTest Unit Test
    $zip = new ZipFile();

    $sampleThemePath = base_path('tests/Unit/__DATA__/Themes/sample-theme');
    $zip->addDirRecursive($sampleThemePath);
    $path = storage_path('framework/testing/sample-theme.zip');
    $zip->saveAsFile($path);

    $zip = new UploadedFile(
        $path,
        'sample-theme.zip',
        'application/zip',
        null,
        true
    );

    $files = $this->callConsoleApi('POST', '/theme', [
        'zip' => $zip,
    ])
        ->assertOk()
        ->json();

    $files = collect($files);

    $templates = $files->where('folder', 'templates');
    expect($templates->firstWhere('name', '@base.twig'))->not()->toBeNull();
    expect($templates->firstWhere('name', 'index.twig'))->not()->toBeNull();
    expect($templates->firstWhere('name', 'no.twig'))->toBeNull();

    expect($files->where('folder', 'styles')->firstWhere('name', 'index.scss'))->not()->toBeNull();
    expect($files->where('folder', 'styles')->firstWhere('name', 'other.scss'))->toBeNull();

    expect(
        $files->where('folder', null)->firstWhere('name', 'config.yaml')['content']
    )->toBe('THEME_NAME=sample');
});
