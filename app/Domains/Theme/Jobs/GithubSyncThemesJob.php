<?php

namespace App\Domains\Theme\Jobs;

use App\Domains\Theme\GithubSync\GithubSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Syncs themes with the GitHub Repo <https://github.com/hyvor/hyvor-blogs-themes>
 */
class GithubSyncThemesJob implements ShouldQueue
{
    use Queueable;

    public function handle()
    {
        $zipBallUrl = "https://github.com/hyvor/hyvor-blogs-themes/zipball/main";
        $zip = file_get_contents($zipBallUrl);

        GithubSyncService::sync($zip);
    }
}
