<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\Events\StylesEditedEvent;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates name', function () {
    $file = ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    $this->callConsoleApi('PATCH', "/theme/file/$file->id", [
        'name' => 'new.twig',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('name', 'new.twig')->etc());
});

it('updates content', function () {
    $file = ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    $this->callConsoleApi('PATCH', "/theme/file/$file->id", [
        'content' => 'new',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('content', 'new')->etc());
});

it('updates content to empty', function () {
    $file = ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    $this->callConsoleApi('PATCH', "/theme/file/$file->id", [
        'content' => '',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('content', null)->etc());
});

it('updates styles with event', function() {

    Event::fake();

    $file = ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::STYLES,
        'index.scss',
        'none'
    );

    $this->callConsoleApi('PATCH', "/theme/file/$file->id", [
        'content' => '',
    ])->assertOk();

    Event::assertDispatched(StylesEditedEvent::class);

});