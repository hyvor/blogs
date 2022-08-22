<?php

namespace App\Http\Controllers\Special;

use App\Domains\Integrations\Github\Webhook\WebhookValidator;
use App\Domains\Theme\GithubSync\GithubSyncService;
use Illuminate\Http\Request;

class GithubThemePingController
{
    public function ping(Request $request)
    {
        WebhookValidator::validate($request, config('services.github.webhook_secret_for_themes'));

        dispatch(function () {
            GithubSyncService::syncFromGithubZipBall();
        });

        return response();
    }
}
