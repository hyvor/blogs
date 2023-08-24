<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\LinkAnalysis\LinkObject;
use App\Domains\LinkAnalyzer\LinkAnalyzeService;
use App\Domains\LinkAnalyzer\LinkStatusTypeEnum;
use App\Domains\Post\PostRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleLinkAnalysisController
{

    public function checkPostVariantLinks(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'post_id' => 'required|integer',
            'language_id' => 'required|integer',
            'urls' => 'required|array',
            'urls.*' => 'required|url',
            'force' => 'boolean',
        ]);

        $postId = $request->integer('post_id');
        $languageId = $request->integer('language_id');

        // if true, will recheck all links, even if they were checked recently
        $force = $request->boolean('force');

        $variant = PostRepository::getPostVariantByPostIdAndLanguageId(
            $postId,
            $languageId
        );

        if (!$variant) {
            throw new TrustedException('Post variant not found');
        }

        /** @var string[] $urls */
        $urls = $request->input('urls');
        $urls = array_slice($urls, 0, 100);

        $fromDb = $force ? [] : LinkAnalyzeService::getFromDb($blog, $urls);
        $urls = array_diff($urls, array_keys($fromDb));

        $fromHttp = LinkAnalyzeService::analyze($urls);
        $fromHttp = LinkAnalyzeService::saveToDb($blog, $variant, $fromHttp);

        $results = array_merge($fromDb, $fromHttp);

        $currentVariantResults = $variant->link_analysis ?? [];
        PostRepository::updatePostVariant($variant, [
            'link_analysis' => array_merge(
                $currentVariantResults,
                $results
            )
        ]);

        return response()->json($results);

    }

    public function ignoreLink(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'url' => 'required|url',
            'status' => 'required|boolean'
        ]);

        $url = (string) $request->string('url');
        $status = $request->boolean('status');
        $link = LinkAnalyzeService::getLink($blog, $url);

        if (!$link)
            throw new TrustedException('Link not found');

        LinkAnalyzeService::ignoreLink($link, $status);

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

}