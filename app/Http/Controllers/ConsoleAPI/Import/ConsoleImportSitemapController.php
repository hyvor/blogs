<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI\Import;

use App\Domains\Import\Importer\Importer;
use App\Domains\Import\Sitemap\PageScraper\PageScraper;
use App\Domains\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Domains\Import\Sitemap\SitemapParser;
use App\Domains\Post\Content\PostContentRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\Import;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function PHPStan\dumpType;

class ConsoleImportSitemapController
{

    private function getPageScraperOptions(Request $request) : PageScraperOptions
    {

        $data = $request->validate([
            'css' => 'array',
            'css.title' => 'string|nullable',
            'css.description' => 'string|nullable',
            'css.content' => 'string|required',
            'css.content_exclude' => 'string|nullable',
            'css.published_date' => 'string|nullable',
            'slug_exclude' => 'string|nullable'
        ]);

        return new PageScraperOptions(
            contentSelector: $data['css']['content'],
            titleSelector: $data['css']['title'] ?? null,
            descriptionSelector: $data['css']['description'] ?? null,
            contentExcludeSelector: $data['css']['content_exclude'] ?? null,
            publishedAtSelector: $data['css']['published_date'] ?? null,
            slugExclude: $data['slug_exclude'] ?? null,
        );

    }

    public function test(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'url' => 'required|string'
        ]);

        $url = (string) $request->string('url');

        $scrapper = new PageScraper(
            $blog,
            $url,
            $this->getPageScraperOptions($request)
        );

        $scrapper->scrape();

        if ($error = $scrapper->getError()) {
            throw new TrustedException($error->value);
        }

        return response()->json([
            'url' => $url,
            'data' => [
                'title' => $scrapper->title,
                'description' => $scrapper->description,
                'content' => $scrapper->content,
                'content_html' => PostContentRepository::getHtml($scrapper->content, $blog),
                'published_at' => $scrapper->publishedAt->getTimestamp(),
                'featured_image_url' => $scrapper->featuredImageUrl,
                'slug' => $scrapper->slug
            ],
            'meta' => [
                'select_type' => [
                    'title' => $scrapper->titleSelectType,
                    'description' => $scrapper->descriptionSelectType,
                    'published_at' => $scrapper->publishedAtSelectType
                ]
            ]
        ]);

    }

    public function import(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'sitemap_url' => 'required|string'
        ]);

        $sitemapUrl = (string) $request->string('sitemap_url');
        $options = $this->getPageScraperOptions($request);

        $importer = new Importer(
            $blog,
            new Import,
            new SitemapParser(
                $blog,
                $sitemapUrl,
                $options
            )
        );
        $importer->import();

        return response()->json();

    }

}