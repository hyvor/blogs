<?php

namespace App\Http\Controllers\Special;

use App\Domains\Theme\GithubSync\GithubSyncService;
use Illuminate\Http\Request;

class GithubThemePingController
{
    public function ping(Request $request)
    {
        /**
         * TODO: Add validation
         */
        dispatch(function () {
            GithubSyncService::syncFromGithubZipBall();
        });
    }
}
