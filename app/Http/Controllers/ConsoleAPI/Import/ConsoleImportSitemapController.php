<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI\Import;

use App\Data\Enums\ImportTypeEnum;
use App\Data\Objects\ConsoleAPI\Import\ImportObject;
use App\Domains\Import\Importer\ImportJob;
use App\Domains\Import\ImportService;
use App\Domains\Import\Sitemap\PageScraper\PageScraper;
use App\Domains\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Domains\Import\Sitemap\SitemapParser;
use App\Domains\Post\Content\PostContentService;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                'content_html' => PostContentService::getHtml($scrapper->content, $blog),
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
            'sitemap_url' => 'required|string',
            'import_images' => 'bool'
        ]);

        $sitemapUrl = (string) $request->string('sitemap_url');
        $importImages = $request->boolean('import_images');
        $options = $this->getPageScraperOptions($request);

        $import = ImportService::createImport(
            $blog,
            ImportTypeEnum::SITEMAP,
            $sitemapUrl
        );

        dispatch(new ImportJob(
            $blog,
            $import,
            new SitemapParser(
                $blog,
                $sitemapUrl,
                $options
            ),
            $importImages
        ));

        return response()->json(new ImportObject($import));

    }

}