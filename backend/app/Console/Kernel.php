<?php

namespace App\Console;

use App\Domains\LinkAnalyzer\Check\DispatchAllChecksJob;
use App\Domains\Post\Jobs\PublishScheduledPosts;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Domains\LinkAnalyzer;

class Kernel extends ConsoleKernel
{

    /**
     * @var string[]
     */
    protected $commands = [


        // app:link-analyzer:run {subdomain}
        LinkAnalyzer\Command\RunLinkAnalyzerCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // app
        $schedule->job(new PublishScheduledPosts())->everyFiveMinutes();
        $schedule->job(new DispatchAllChecksJob())->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        // require base_path('routes/console.php');
    }
}
