<?php

namespace App\Console;

use App\Domains\Integrations\EmailOctopus\EmailOctopusSyncJob;
use App\Domains\Post\Jobs\PublishScheduledPosts;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /*protected $commands = [

    ];*/

    protected function schedule(Schedule $schedule)
    {
        // app
        $schedule->job(new PublishScheduledPosts())->everyFiveMinutes();

        // marketing
        $schedule->job(EmailOctopusSyncJob::class)->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
