<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Theme\Events\ConfigEditedEvent;
use App\Domains\Theme\Events\StylesEditedEvent;
use App\Domains\Theme\Events\TemplateEditedEvent;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates name', function () {

    $blog = blogWithAccess();

    $file = ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    consoleApi($blog, 'PATCH', "/theme/file/$file->id", [
        'name' => 'new.twig',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('name', 'new.twig')->etc());
});

it('updates content', function () {
    Event::fake();


    $blog = blogWithAccess();

    $file = ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    consoleApi($blog, 'PATCH', "/theme/file/$file->id", [
        'content' => 'new',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('content', 'new')->etc());

    Event::assertDispatched(TemplateEditedEvent::class);
});

it('updates a style file', function() {
    Event::fake();

    $blog = blogWithAccess();

    $file = ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::STYLES,
        'index.scss',
        'none'
    );

    consoleApi($blog, 'PATCH', "/theme/file/$file->id", [
        'content' => 'new',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('content', 'new')->etc());

    Event::assertDispatched(StylesEditedEvent::class);
});

it('updates content to empty', function () {
    $blog = blogWithAccess();

    $file = ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'none'
    );

    consoleApi($blog, 'PATCH', "/theme/file/$file->id", [
        'content' => '',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('content', null)->etc());
});

it('updates styles with event', function () {
    Event::fake();

    $blog = blogWithAccess();

    $file = ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::STYLES,
        'index.scss',
        'none'
    );

    consoleApi($blog, 'PATCH', "/theme/file/$file->id", [
        'content' => '',
    ])->assertOk();

    Event::assertDispatched(StylesEditedEvent::class);
});

it('updates config with event', function() {

    Event::fake();

    $blog = blogWithAccess();

    $file = ThemeFilesRepository::createOrUpdateFile(
        $blog,
        null,
        'config.yaml',
        'none'
    );

    consoleApi($blog, 'PATCH', "/theme/file/$file->id", [
        'content' => '',
    ])->assertOk();

    Event::assertDispatched(ConfigEditedEvent::class);

});
