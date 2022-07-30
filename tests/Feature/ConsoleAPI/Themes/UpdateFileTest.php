<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates name', function() {

    $file = ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    $this->callConsoleApi('PUT', "/theme/file/$file->id", [
        'name' => 'new.twig'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('name', 'new.twig')->etc());

});

it('updates content', function() {

    $file = ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    $this->callConsoleApi('PUT', "/theme/file/$file->id", [
        'content' => 'new'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('content', 'new')->etc());

});

it('updates content to empty', function() {

    $file = ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    $this->callConsoleApi('PUT', "/theme/file/$file->id", [
        'content' => ''
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('content', null)->etc());

});