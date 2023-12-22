<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use PhpZip\ZipFile;

it('downloads the theme as a zip', function () {
    $blog = blogWithAccess();
    $content = 'Hi';

    ThemeFilesRepository::createOrUpdateFile($blog, ThemeFileFolderEnum::TEMPLATES, 'index.twig', $content);
    ThemeFilesRepository::createOrUpdateFile($blog, null, 'config.yaml', '');

    $zip = consoleApi($blog, 'GET', '/theme/download');

    $zipFile = new ZipFile();
    $zipFile->openFromString($zip->streamedContent());

    expect($zipFile['templates/index.twig'])->toBe($content);
    expect($zipFile['config.yaml'])->toBe('');
});
