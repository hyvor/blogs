<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\ThemeFile;

it('deletes a file', function () {

    $blog = blogWithAccess();

    $file = ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    consoleApi($blog, 'DELETE', "/theme/file/$file->id")
        ->assertOk();

    expect(ThemeFile::find($file->id))->toBeNull();
});
