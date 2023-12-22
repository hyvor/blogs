<?php declare(strict_types=1);

namespace App\Domains\Theme\GithubSync;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GithubSyncJob implements ShouldQueue
{

    use Dispatchable;

    public function handle() : void
    {
        GithubSyncService::syncFromGithubZipBall();
    }

}