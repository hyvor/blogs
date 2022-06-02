<?php

namespace Tests\Unit\Domains\Themes\GithubSync;

/*it('downloads', function() {

    $zipball = "https://github.com/hyvor/hyvor-blogs-themes/zipball/main";
    $zip = file_get_contents($zipball);
    file_put_contents(base_path('tests/Unit/__DATA__/Themes/github-themes.zip'), $zip);

});*/

use App\Domains\Theme\GithubSync\GithubSyncService;
use App\Models\Theme;
use App\Models\ThemeVersion;

it('adds new themes and versions', function() {

    $zip = file_get_contents(test_unit_data_path('Themes/github-themes.zip'));
    GithubSyncService::sync($zip);

    expect(Theme::count())->toBeGreaterThan(0);

    $themes = Theme::get();

    foreach ($themes as $theme) {
        expect(ThemeVersion::where('theme_id', $theme->id)->first())->not->toBeNull();
    }

});