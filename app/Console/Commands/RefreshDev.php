<?php

namespace App\Console\Commands;

use App\Domains\Theme\Jobs\GithubSyncThemesJob;
use Illuminate\Console\Command;

class RefreshDev extends Command
{
    protected $signature = 'refresh:dev {--no-seed}';
    protected $description = 'Refresh the DB and Themes for development';

    public function handle()
    {
        $noSeed = $this->option('no-seed');
        $this->call('migrate:fresh', ['--seed' => !$noSeed]);

        $this->comment('Downloading themes');
        dispatch(new GithubSyncThemesJob());
        $this->info('Themes downloaded');
    }
}
