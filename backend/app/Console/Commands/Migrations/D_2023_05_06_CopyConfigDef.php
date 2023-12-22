<?php declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\Theme\ThemeRepository;
use App\Models\Blog;
use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Console\Command;
use PhpZip\ZipFile;

/**
 * Copies config.def.yaml from the latest version of the current theme of the blog
 */
class D_2023_05_06_CopyConfigDef extends Command
{

    public $name = 'migrate:copy-config-def';

    public function handle() : void
    {

        $blogs = Blog::where('type', 'default')->get();

        foreach ($blogs as $blog) {

            $themeVersion = ThemeVersion::find($blog->theme_version_id);

            if (!$themeVersion) {
                $this->info('Skipping blog '.$blog->subdomain.' because it has no theme version');
                continue;
            }

            $theme = Theme::find($themeVersion->theme_id);

            if (!$theme) {
                $this->info('Skipping blog '.$blog->subdomain.' because it has no theme');
                continue;
            }

            $latestVersion = ThemeRepository::getThemeLatestVersion($theme);

            if (!$latestVersion) {
                $this->info('Skipping blog '.$blog->subdomain.' because it has no latest theme version');
                continue;
            }

            $zip = new ZipFile();
            $zip->openFromString($latestVersion->zip);

            $configDef = $zip->getEntryContents('config.def.yaml');

            if (!$configDef) {
                $this->info('Skipping blog '.$blog->subdomain.' because latest version has no config.def.yaml');
                continue;
            }

            ThemeFilesRepository::createOrUpdateFile($blog, null, 'config.def.yaml', $configDef);

            $this->info('Successfully copied config.def.yaml for blog '.$blog->subdomain);

        }

    }

}