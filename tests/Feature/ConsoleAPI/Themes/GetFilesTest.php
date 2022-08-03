<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets files', function () {
    ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    $this->callConsoleApi('GET', '/theme/files')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->count(1)
                ->first(fn (AssertableJson $json) => $json->where('name', 'index.twig')->etc())
        );
});
