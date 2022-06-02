<?php

namespace App\Console\Commands;

use App\Domains\Theme\Jobs\GithubSyncThemesJob;
use Illuminate\Console\Command;

class RefreshDev extends Command
{

    protected $signature = 'refresh:dev';
    protected $description = 'Refresh the DB and Themes for development';

    public function handle()
    {

        $this->call('migrate:fresh', ['--seed' => true]);

        $this->comment('Downloading themes');
        dispatch(new GithubSyncThemesJob);
        $this->info('Themes downloaded');

    }

}