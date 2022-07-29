<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a file', function() {

    $this->callConsoleApi('POST', '/theme/file', [
        'folder' => 'templates',
        'name' => 'index.twig',
        'content' => 'test'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('name', 'index.twig')
                ->where('content', 'test')
                ->etc()
        );

});

it('does not create a file if one already exists', function() {

    ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    $this->callConsoleApi('POST', '/theme/file', [
        'folder' => 'templates',
        'name' => 'index.twig',
        'content' => 'test'
    ])
        ->assertUnprocessable()
        ->assertSee('File already exists');

});