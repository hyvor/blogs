<?php declare(strict_types=1);

namespace App\Http\Controllers\Special;

use App\Domains\Theme\GithubSync\GithubSyncJob;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GithubThemeController
{
    public function publish(Request $request) : JsonResponse
    {

        $key = strval($request->header('X-Key'));

        if ($key !== strval(config('services.github.themes_publish_key'))) {
            throw new Exception('Invalid Github Themes Publish Key');
        }

        GithubSyncJob::dispatch();

        return response()->json([
            'status' => 'ok'
        ]);
    }
}
