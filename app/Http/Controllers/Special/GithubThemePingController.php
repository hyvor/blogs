<?php declare(strict_types=1);

namespace App\Http\Controllers\Special;

use App\Domains\Integrations\Github\Webhook\WebhookValidator;
use App\Domains\Theme\GithubSync\GithubSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GithubThemePingController
{
    public function ping(Request $request) : JsonResponse
    {
        WebhookValidator::validate(
            $request,
            strval(config('services.github.webhook_secret_for_themes'))
        );

        dispatch(function () {
            GithubSyncService::syncFromGithubZipBall();
        });

        return response()->json();
    }
}
