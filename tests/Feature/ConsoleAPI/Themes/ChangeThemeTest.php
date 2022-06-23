<?php

namespace Tests\Feature\ConsoleAPI\Themes;

use App\Models\Theme;
use App\Models\ThemeFile;
use App\Models\ThemeVersion;

it('changes the theme of a blog with the latest version', function () {
    $theme = Theme::factory()
        ->has(ThemeVersion::factory()->count(2), 'versions')
        ->create();

    ThemeFile::factory()->create(['blog_id' => blog(), 'name' => 'hello.twig']);

    $json = $this->callConsoleApi('PATCH', '/theme', [
        'name' => $theme->name,
    ])
        ->assertOk()
        ->json();

    $json = collect($json);

    $versions = $theme->versions;
    $versionNumber = $versions->sortByDesc('id')->first()->version;

    expect($json->firstWhere('name', 'config.yaml')['content'])->toContain($versionNumber);
    expect(ThemeFile::where('blog_id', blog()->id)->where('name', 'hello.twig')->first())->toBeNull();
});
