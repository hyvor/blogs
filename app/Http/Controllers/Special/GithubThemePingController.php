<?php

namespace App\Http\Controllers\Special;

use App\Domains\Theme\Jobs\GithubSyncThemesJob;
use Illuminate\Http\Request;

class GithubThemePingController
{
    public function ping(Request $request)
    {
        /**
         * TODO: Add validation
         */
        dispatch(new GithubSyncThemesJob());
    }
}
