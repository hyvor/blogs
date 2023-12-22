<?php declare(strict_types=1);

namespace Tests\Feature\Commands\Migrations;

use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Theme;
use App\Models\ThemeVersion;
use PhpZip\ZipFile;

it('copies config def', function() {

    $blog = blog();

    $theme = Theme::factory()->create();

    $newZip = new ZipFile();
    $newZip->addFromString('config.def.yaml', 'config: def');

    $themeOldVersion = ThemeVersion::factory()->create(['theme_id' => $theme]);
    $themeNewVersion = ThemeVersion::factory()->create([
        'theme_id' => $theme,
        'zip' => $newZip->outputAsString(),
    ]);

    $blog->theme_version_id = $themeOldVersion->id;
    $blog->save();

    $this->artisan('migrate:copy-config-def');

    $configDef = ThemeFilesRepository::getFile($blog,'config.def.yaml');
    expect($configDef->content)->toBe('config: def');

});