<?php

namespace App\Console\Commands;

use App\Domains\Theme\GithubSync\GithubSyncService;
use Illuminate\Console\Command;

class DownloadThemes extends Command
{
    protected $signature = 'download:themes';

    protected $description = 'Download themes';

    public function handle()
    {
        $this->comment('Downloading themes');
        GithubSyncService::syncFromGithubZipBall();
        $this->info('Themes downloaded');
    }
}
