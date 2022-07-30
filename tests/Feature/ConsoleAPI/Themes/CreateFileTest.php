<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Http\UploadedFile;
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

it('creates a file from blob', function() {

    $file = UploadedFile::fake()->image('test.jpg');

    $this->callConsoleApi('POST', '/theme/file', [
        'folder' => 'assets',
        'name' => 'test.jpg',
        'file' => $file
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('name', 'test.jpg')
                ->where('content', null)
                ->etc()
        );

});

it('validates file size', function() {

    $file = UploadedFile::fake()->image('test.jpg')->size(config('limits.max_asset_file_size') / 1000 + 1);

    $this->callConsoleApi('POST', '/theme/file', [
        'folder' => 'assets',
        'name' => 'test.jpg',
        'file' => $file
    ])
        ->assertUnprocessable()
        ->assertSee('The file must not be greater than');

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