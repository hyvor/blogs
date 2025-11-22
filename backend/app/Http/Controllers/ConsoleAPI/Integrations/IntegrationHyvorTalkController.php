<?php
declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI\Integrations;

use App\Data\Objects\ConsoleAPI\Integration\HyvorTalk\GatedContentRuleObject;
use App\Data\Objects\ConsoleAPI\Integration\HyvorTalk\HyvorTalkIntegrationObject;
use App\Domains\Integrations\HyvorTalk\HyvorTalkGatedContentService;
use App\Domains\Integrations\HyvorTalk\HyvorTalkService;
use App\Domains\Tag\TagRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\HyvorTalkGatedContentRule;
use App\Models\HyvorTalkWebsite;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationHyvorTalkController
{

    public function __construct(
        private readonly HyvorTalkService $hyvorTalkService
    ) {
    }

    public function getIntegration(Blog $blog): JsonResponse
    {
        $hyvorTalkWebsite = $this->hyvorTalkService->getHyvorTalkWebsite($blog);

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

    private function getHyvorTalkWebsite(Blog $blog): HyvorTalkWebsite
    {
        $hyvorTalkWebsite = HyvorTalkWebsite::where('blog_id', $blog->id)->first();

        if (!$hyvorTalkWebsite) {
            throw new HttpException('hyvor_talk_not_connected');
        }

        return $hyvorTalkWebsite;
    }

    public function createIntegration(Blog $blog): JsonResponse
    {
        $hyvorTalkWebsite = $this->hyvorTalkService->getHyvorTalkWebsite($blog);

        if ($hyvorTalkWebsite) {
            throw new TrustedException('Hyvor Talk integration already exists');
        }

        // TODO: Org
        if (!$blog->hyvor_user_id) {
            throw new TrustedException('Hyvor Talk integration requires Hyvor Talk user ID');
        }

        $hyvorTalkWebsite = $this->hyvorTalkService->createHyvorTalkWebsite($blog);

        return response()->json(new HyvorTalkIntegrationObject($hyvorTalkWebsite));
    }

    public function deleteIntegration(Blog $blog): JsonResponse
    {
        $hyvorTalkWebsite = $this->getHyvorTalkWebsite($blog);

        $this->hyvorTalkService->deleteHyvorTalkWebsite($hyvorTalkWebsite);

        return response()->json();
    }

    public function getGatedContentRules(Blog $blog): JsonResponse
    {
        $rules = HyvorTalkGatedContentService::getGatedContentRules($blog, withTag: true)
            ->map(fn($rule) => new GatedContentRuleObject($rule, $blog));

        return response()->json($rules);
    }

    public function createGatedContentRule(Blog $blog, Request $request): JsonResponse
    {
        $request->validate([
            'tag_id' => 'required|integer',
            'minimum_plan' => 'required|string',
            'gate' => 'string|nullable'
        ]);

        if (
            HyvorTalkGatedContentService::getGatedContentRulesCount($blog) >=
            HyvorTalkGatedContentService::MAX_GATED_CONTENT_RULES
        ) {
            throw new TrustedException('Maximum number of gated content rules reached');
        }

        $tagId = $request->integer('tag_id');
        $minimumPlan = (string)$request->string('minimum_plan');
        /** @var ?string $gate */
        $gate = $request->input('gate');

        if (HyvorTalkGatedContentService::ruleByBlogAndTagId($blog, $tagId)) {
            throw new TrustedException('Gated content rule already exists for this tag');
        }

        $rule = HyvorTalkGatedContentService::createGatedContentRule(
            $blog,
            $tagId,
            $minimumPlan,
            $gate
        );

        return response()->json(new GatedContentRuleObject($rule, $blog));
    }

    public function updateGatedContentRule(HyvorTalkGatedContentRule $rule, Blog $blog, Request $request): JsonResponse
    {
        $request->validate([
            'minimum_plan' => 'required|string',
            'gate' => 'string|nullable'
        ]);

        $updates = [];

        if ($request->has('minimum_plan')) {
            $updates['minimum_plan'] = (string)$request->string('minimum_plan');
        }

        if ($request->has('gate')) {
            $updates['gate'] = $request->input('gate');
        }

        $rule = HyvorTalkGatedContentService::updateGatedContentRule($blog, $rule, $updates);

        return response()->json(new GatedContentRuleObject($rule, $blog));
    }


    public function deleteGatedContentRule(HyvorTalkGatedContentRule $rule, Blog $blog): JsonResponse
    {
        HyvorTalkGatedContentService::deleteGatedContentRule($blog, $rule);
        return response()->json();
    }

    public function getMembershipPlans(Blog $blog): JsonResponse
    {
        $htWebsite = $this->getHyvorTalkWebsite($blog);

        try {
            $website = $this->hyvorTalkService->callConsoleApi($htWebsite, 'GET', '/website');

            if ($website['memberships_enabled'] !== true) {
                throw new HttpException('memberships_not_enabled');
            }

            $plans = $this->hyvorTalkService->callConsoleApi($htWebsite, 'GET', '/membership-plans');
        } catch (InternalApiCallFailedException $e) {
            throw new HttpException('Failed to get membership plans from Hyvor Talk');
        }

        return response()->json([
            'currency' => $website['memberships_currency'],
            'plans' => collect($plans)->map(fn($plan) => [
                'name' => $plan['name'],
                'monthly_price' => $plan['monthly_price'],
            ])
        ]);
    }

}