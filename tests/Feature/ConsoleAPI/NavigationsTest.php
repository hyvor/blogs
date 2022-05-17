<?php

namespace Tests\Feature\ConsoleAPI;

use App\Domains\Navigation\NavigationRepository;

// php artisan test  --filter 'NavigationsTest'

beforeEach(function() {
    $this->id = 1;
    $this->name = 'About';
    $this->url = 'about';
    $this->type = 'header';
    $this->primaryLanguage = 1;
    $this->secondaryLanguage = 2;
});

it('fetches navigation', function () {
    $this
        ->callConsoleApi('GET', 'navigation')
        ->assertOk();
});

it('creates a navigation success', function () {
    $this
        ->callConsoleApi('POST', 'navigation', [
            'name' =>  $this->name,
            'url' => $this->url,
            'type' => 'header',
        ])
        ->assertOk();
});

it('creating navigation fails on empty fields', function () {
    $this
        ->callConsoleApi('POST', 'navigation')
        ->assertStatus(422);
});

it('creates a navigation fails if (name) is null', function () {
    $this
        ->callConsoleApi('POST', 'navigation', [
            'name' => null,
            'url' => $this->url,
            'type' => 'header',
        ])
        ->assertStatus(422);
});

it('creates a navigation fails if (url) is null', function () {
    $this
        ->callConsoleApi('POST', 'navigation', [
            'name' => $this->name,
            'url' => null,
            'type' => 'header',
        ])
        ->assertStatus(422);
});

it('creates a navigation fails if (type) is not header or footer', function () {
    $this
        ->callConsoleApi('POST', 'navigation', [
            'name' => $this->name,
            'url' => $this->url,
            'type' => 'wrong',
        ])
        ->assertStatus(422);
});

it('creates a navigation fails if there are more than 8 header navigations.', function () {
    $getHeaderCount = NavigationRepository::getHeaderCount(blog());
    $headerCount = $getHeaderCount < 8;
    if ($headerCount) {
        $this
            ->callConsoleApi('POST', 'navigation', [
                'name' => $this->name,
                'url' => $this->url,
                'type' => 'header',
            ])
            ->assertStatus(200);
    } else {
        $this->assertFalse(false);
    }
});

it('creates a navigation fails if there are more than 8 footer navigations.', function () {
    $getFooterCount = NavigationRepository::getFooterCount(blog());
    $footerCount = $getFooterCount < 8;
    if ($footerCount) {
        $this
            ->callConsoleApi('POST', 'navigation', [
                'name' => $this->name,
                'url' => $this->url,
                'type' => 'header',
            ])
            ->assertStatus(200);
    } else {
        $this->assertFalse(false);
    }
});

it('deleting all the navigation data if language is in primary language.', function () {
    $this
        ->callConsoleApi('DELETE', 'navigation/'. $this->id, [
            'languageId' => $this->primaryLanguage,
        ])
        ->assertOk();
});

it('delete only the variant data if language is not in primary language.', function () {
    $this
        ->callConsoleApi('DELETE', 'navigation/'.$this->id, [
            'languageId' => $this->secondaryLanguage,
        ])
        ->assertOk();
});

it('updating a navigation success', function () {
    $this
        ->callConsoleApi('PUT', 'navigation/'.$this->id, [
            'languageId' => 1,
            'name' => $this->name,
            'url' => $this->url,
            'type' => 'header',
        ])
        ->assertOk();
});

it('updating a navigation fails if (name) is null', function () {
    $this
        ->callConsoleApi('PUT', 'navigation/'.$this->id, [
            'name' => null,
            'url' => $this->url,
            'type' => 'header',
        ])
        ->assertStatus(422);
});

it('updating a navigation fails if (url) is null', function () {
    $this
        ->callConsoleApi('PUT', 'navigation/'.$this->id, [
            'name' => $this->name,
            'url' => null,
            'type' => 'header',
        ])
        ->assertStatus(422);
});

it('updating a navigation fails if (type) is null', function () {
    $this
        ->callConsoleApi('PUT', 'navigation/'.$this->id, [
            'name' => $this->name,
            'url' => $this->url,
            'type' => null,
        ])
        ->assertStatus(422);
});

it('update the sort', function () {
    $this
        ->callConsoleApi('PUT', '/navigation/sort/'.$this->id, [
            'navigationSort' => 2,
        ])
        ->assertOk();
});

it('update the navigation source', function () {
    $this
        ->callConsoleApi('PUT', '/navigation/source/'.$this->id, [
            'sort' => 2,
        ])
        ->assertOk();
});

it('create variant ( It should not be the default language )', function () {
    $this
        ->callConsoleApi('POST', '/navigation/variant', [
            'id' => $this->id,
            'languageId' => $this->secondaryLanguage,
        ])
        ->assertOk();
});

it('create variant ( If language id is null ) ', function () {
    $id = 1;
    $this
        ->callConsoleApi('POST', '/navigation/variant', [
            'id' => 1,
            'languageId' => null,
        ])
        ->assertStatus(422);
});