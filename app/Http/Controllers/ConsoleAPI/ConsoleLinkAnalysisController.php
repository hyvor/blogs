<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Domains\LinkAnalyzer\LinkAnalyzerService;
use App\Domains\Post\PostRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $fromDb = $force ? [] : LinkAnalyzerService::getFromDb($blog, $urls);
        $urls = array_diff($urls, array_keys($fromDb));

        $fromHttp = LinkAnalyzerService::analyze($urls);
        $fromHttp = LinkAnalyzerService::saveToDb($blog, $fromHttp);

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
        $link = LinkAnalyzerService::getLink($blog, $url);

        if (!$link)
            throw new TrustedException('Link not found');

        LinkAnalyzerService::ignoreLink($link, $status);

        return response()->json([
            'status' => $link->ignore ? LinkAnalyzerService::IGNORE_CODE : $link->status_code
        ]);
    }

}