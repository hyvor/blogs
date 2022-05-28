<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use PhpZip\ZipFile;

it('downloads', function() {

    $blog = blog();
    $content = 'Hi';

    ThemeFilesRepository::createOrUpdateFile($blog, ThemeFileFolderEnum::TEMPLATES, 'index.twig', $content);

    $zip = $this->callConsoleApi('GET', '/theme/download');

    $zipFile = new ZipFile();
    $zipFile->openFromString($zip->streamedContent());

    expect($zipFile['templates/index.twig'])->toBe($content);

});