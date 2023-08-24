<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\JobStatusEnum;
use App\Data\Objects\ConsoleAPI\LinkAnalysis\CheckObject;
use App\Data\Objects\ConsoleAPI\LinkAnalysis\LinkObject;
use App\Domains\LinkAnalyzer\Check\AnalyzeAllLinksJob;
use App\Domains\LinkAnalyzer\Check\LinkAnalyzerCheckService;
use App\Domains\LinkAnalyzer\LinkAnalyzeService;
use App\Domains\LinkAnalyzer\LinkStatusTypeEnum;
use App\Domains\LinkAnalyzer\PostVariantLinkService;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\PostVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleLinkAnalysisController
{

    public function checkPostVariantLinks(Request $request, Blog $blog, PostVariant $postVariant) : JsonResponse
    {

        $request->validate([
            'post_variant_id' => 'required|integer',
            'urls' => 'required|array',
            'urls.*' => 'required|url',
            'force' => 'boolean',
        ]);

        // if true, will recheck all links, even if they were checked recently
        $force = $request->boolean('force');

        /** @var string[] $urls */
        $urls = $request->input('urls');
        $urls = array_slice($urls, 0, 100);

        // $fromDb = $force ? [] : LinkAnalyzeService::getFromDb($blog, $urls);
        // $urls = array_diff($urls, array_keys($fromDb));

        $fromHttp = LinkAnalyzeService::analyze($urls);
        $links = PostVariantLinkService::updateLinksFromResults($blog, $postVariant, $fromHttp);
        $results = LinkAnalyzeService::getResultsFromLinks($links);

        // $results = array_merge($fromDb, $fromHttp);
        PostVariantLinkService::updatePostVariantCache($postVariant, $results, true);

        return response()->json($results);
    }

    public function ignoreLink(Request $request, Blog $blog, PostVariant $postVariant) : JsonResponse
    {

        $request->validate([
            'post_variant_id' => 'required|integer',
            'url' => 'required|url',
            'status' => 'required|boolean'
        ]);

        $url = (string) $request->string('url');
        $status = $request->boolean('status');
        $link = PostVariantLinkService::getLink($postVariant, $url);

        if (!$link)
            throw new TrustedException('Link not found');

        PostVariantLinkService::ignoreLink($link, $status);

        return response()->json([
            'status' => $link->ignore ? LinkAnalyzeService::IGNORE_CODE : $link->status_code
        ]);
    }

    public function getStats(Blog $blog) : JsonResponse
    {

        $counts = LinkAnalyzeService::getCountsByStatus($blog);

        return response()->json([
            'counts' => $counts
        ]);

    }

    public function getLinks(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'type' => [new Enum(LinkStatusTypeEnum::class), 'nullable'],
            'limit' => 'integer',
            'offset' => 'integer',
        ]);

        $type = LinkStatusTypeEnum::tryFrom((string) $request->string('type'));
        $limit = $request->integer('limit', 50);
        $offset = $request->integer('offset');

        $links = LinkAnalyzeService::getLinksOfBlog(
            $blog,
            $type,
            $limit,
            $offset
        )->mapInto(LinkObject::class);

        return response()->json($links);

    }

    public function getChecks(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'limit' => 'integer',
            'offset' => 'integer',
        ]);

        $limit = $request->integer('limit', 50);
        $offset = $request->integer('offset');

        $checks = LinkAnalyzerCheckService::getChecks($blog, $limit, $offset)
            ->mapInto(CheckObject::class);

        return response()->json($checks);

    }

    public function startCheck(Blog $blog) : JsonResponse
    {

        $lastCheck = LinkAnalyzerCheckService::getLastCheck($blog);

        if ($lastCheck && $lastCheck->status === JobStatusEnum::PENDING) {
            throw new TrustedException('A check is already running');
        }

        if ($lastCheck && $lastCheck->created_at->diffInHours() < 24) {
            throw new TrustedException('A check has already run in the last 24 hours');
        }

        $job = new AnalyzeAllLinksJob($blog);
        dispatch($job);

        return response()->json(new CheckObject($job->check));

    }

}