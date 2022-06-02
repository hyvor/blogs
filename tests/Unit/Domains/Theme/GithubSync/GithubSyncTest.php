<?php

namespace Tests\Unit\Domains\Themes\GithubSync;



use App\Domains\Theme\GithubSync\GithubSyncService;
use App\Models\Theme;
use App\Models\ThemeVersion;

/**
 * ZIP Contents
 *
 * original/default
 * original/blank
 */

it('adds new themes and versions', function() {

    $zip = file_get_contents(test_unit_data_path('Themes/github-themes.zip'));
    GithubSyncService::sync($zip);

    expect(Theme::count())->toBeGreaterThan(0);

    $themes = Theme::get();

    foreach ($themes as $theme) {
        expect(ThemeVersion::where('theme_id', $theme->id)->first())->not->toBeNull();
    }

});

it('updates themes if the version number is new', function() {

    $theme = Theme::factory()
        ->has(ThemeVersion::factory(), 'versions')
        ->create([
            'name' => 'default'
        ]);

    $zip = file_get_contents(test_unit_data_path('Themes/github-themes.zip'));
    GithubSyncService::sync($zip);

    expect(ThemeVersion::where('theme_id', $theme->id)->count())->toBe(2);

});

it('does not update if the version is the same', function() {

    $theme = Theme::factory()
        ->has(ThemeVersion::factory()->state(['version' => '1.0.0']), 'versions')
        ->create([
            'name' => 'default'
        ]);

    $zip = file_get_contents(test_unit_data_path('Themes/github-themes.zip'));
    GithubSyncService::sync($zip);

    expect(ThemeVersion::where('theme_id', $theme->id)->count())->toBe(1);

});