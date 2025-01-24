<?php

namespace App\Console;

use App\Domains\App\Marketing\Trial\TrialEmailsJob;
use App\Domains\LinkAnalyzer\Check\DispatchAllChecksJob;
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
        $schedule->job(new DispatchAllChecksJob())->daily();

        // marketing
        $schedule->job(TrialEmailsJob::class)->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
