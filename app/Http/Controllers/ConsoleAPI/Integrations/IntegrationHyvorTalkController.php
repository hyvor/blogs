<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI\Integrations;

use App\Data\Objects\ConsoleAPI\Integration\HyvorTalk\HyvorTalkIntegrationObject;
use App\Domains\Integrations\HyvorTalk\HyvorTalkService;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\HyvorTalkWebsite;
use Illuminate\Http\JsonResponse;

class IntegrationHyvorTalkController
{

    public function getIntegration(Blog $blog) : JsonResponse
    {
        $hyvorTalkWebsite = HyvorTalkService::getHyvorTalkWebsite($blog);

        if (!$hyvorTalkWebsite) {
            return response()->json([
                'connected' => false,
            ]);
        }

        return response()->json([
            'connected' => true,
            'data' => new HyvorTalkIntegrationObject($hyvorTalkWebsite)
        ]);
    }

    public function createIntegration(Blog $blog) : JsonResponse
    {

        $hyvorTalkWebsite = HyvorTalkService::getHyvorTalkWebsite($blog);

        if ($hyvorTalkWebsite) {
            throw new TrustedException('Hyvor Talk integration already exists');
        }

        if (!$blog->hyvor_user_id) {
            throw new TrustedException('Hyvor Talk integration requires Hyvor Talk user ID');
        }

        $hyvorTalkWebsite = HyvorTalkService::createHyvorTalkWebsite($blog);

        return response()->json(new HyvorTalkIntegrationObject($hyvorTalkWebsite));

    }

    public function deleteIntegration(Blog $blog) : JsonResponse
    {
        $hyvorTalkWebsite = HyvorTalkService::getHyvorTalkWebsite($blog);

        if (!$hyvorTalkWebsite) {
            throw new TrustedException('Hyvor Talk integration does not exist');
        }

        HyvorTalkService::deleteHyvorTalkWebsite($hyvorTalkWebsite);

        return response()->json();
    }

}