<?php

namespace App\Console\Commands;

use App\Domains\Theme\GithubSync\GithubSyncService;
use Illuminate\Console\Command;

class RefreshDev extends Command
{
    protected $signature = 'refresh:dev {--no-seed}';

    protected $description = 'Refresh the DB and Themes for development';

    public function handle() : void
    {
        $noSeed = $this->option('no-seed');
        $this->call('migrate:fresh', ['--seed' => ! $noSeed]);

        $this->comment('Downloading themes');
        GithubSyncService::syncFromGithubZipBall();
        $this->info('Themes downloaded');
    }
}
