<?php

namespace Tests\Feature\Pages;

use App\Models\Theme;
use App\Models\ThemeVersion;

it('loads the hello theme at /', function() {

    $blog = blogWithAccessLanguageAndRoutes();

    $theme = Theme::create([
        'name' => 'hello',
        'type' => 'original'
    ]);

    ThemeVersion::factory()->create([
        'theme_id' => $theme->id,
        'preview_subdomain' => $blog->subdomain
    ]);

    $this->get('/themes')
        ->assertStatus(200)
        ->assertSee("<iframe src=\"//$blog->subdomain.hyvorblogs.io\">", false);

});

it('loads other themes', function() {

    $blog = blogWithAccessLanguageAndRoutes();

    $theme = Theme::create([
        'name' => 'ghost-attila',
        'type' => 'original'
    ]);

    ThemeVersion::factory()->create([
        'theme_id' => $theme->id,
        'preview_subdomain' => $blog->subdomain
    ]);

    $this->get('/themes/ghost-attila')
        ->assertStatus(200)
        ->assertSee("<iframe src=\"//$blog->subdomain.hyvorblogs.io\">", false);

});