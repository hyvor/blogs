<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Import\SitemapImportInput;
use App\Api\Console\Input\Import\SitemapTestInput;
use App\Api\Console\Object\Import\ImportObject;
use App\Entity\Enum\ImportType;
use App\Service\AppConfig;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportMessage;
use App\Service\Import\Sitemap\PageScraper\PageScraper;
use App\Service\Import\Sitemap\PageScraper\PageScraperOptions;
use App\Service\Post\Content\PostContentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportSitemapController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private ImportService $importService,
        private MessageBusInterface $bus,
        private HttpClientInterface $httpClient,
        private PostContentService $postContentService,
        private AppConfig $appConfig,
    ) {
    }

    /**
     * @param array<string, string|null> $css
     */
    private function getPageScraperOptions(array $css, ?string $slugExclude): PageScraperOptions
    {
        $contentSelector = $css['content'] ?? null;

        if (!is_string($contentSelector) || $contentSelector === '') {
            throw new UnprocessableEntityHttpException('css.content is required');
        }

        return new PageScraperOptions(
            contentSelector: $contentSelector,
            titleSelector: $css['title'] ?? null,
            descriptionSelector: $css['description'] ?? null,
            contentExcludeSelector: $css['content_exclude'] ?? null,
            publishedAtSelector: $css['published_date'] ?? null,
            slugExclude: $slugExclude,
        );
    }

    #[Route('/data/import/sitemap/test', methods: ['POST'])]
    #[ScopeRequired(Scope::IMPORT_MANAGE)]
    public function test(#[MapRequestPayload] SitemapTestInput $input): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();
        $options = $this->getPageScraperOptions($input->css, $input->slug_exclude);

        $scraper = new PageScraper(
            $blog,
            $input->url,
            $options,
            $this->httpClient,
            $this->postContentService,
            $this->appConfig->getHttpBotUserAgent(),
        );
        $scraper->scrape();

        if ($error = $scraper->getError()) {
            throw new UnprocessableEntityHttpException($error->value);
        }

        return new JsonResponse([
            'url' => $input->url,
            'data' => [
                'title' => $scraper->title,
                'description' => $scraper->description,
                'content' => $scraper->content,
                'content_html' => $this->postContentService->getHtml($scraper->content, $blog),
                'published_at' => $scraper->publishedAt->getTimestamp(),
                'featured_image_url' => $scraper->featuredImageUrl,
                'slug' => $scraper->slug,
            ],
            'meta' => [
                'select_type' => [
                    'title' => $scraper->titleSelectType->value,
                    'description' => $scraper->descriptionSelectType->value,
                    'published_at' => $scraper->publishedAtSelectType->value,
                ],
            ],
        ]);
    }

    #[Route('/data/import/sitemap/import', methods: ['POST'])]
    #[ScopeRequired(Scope::IMPORT_MANAGE)]
    public function import(#[MapRequestPayload] SitemapImportInput $input): JsonResponse
    {
        $blog = $this->blogAuthListener->getBlog();

        if ($this->importService->hasPendingImports($blog)) {
            throw new UnprocessableEntityHttpException('There is already an import in progress for this blog.');
        }

        $options = $this->getPageScraperOptions($input->css, $input->slug_exclude);

        $import = $this->importService->createImport(
            $blog,
            ImportType::SITEMAP,
            $input->sitemap_url,
            [
                'sitemap_url' => $input->sitemap_url,
                'import_images' => $input->import_images,
                'scraper_options' => $options->toArray(),
            ],
        );

        $this->bus->dispatch(new ImportMessage(
            $import->getId(),
            $input->sitemap_url,
            $input->import_images,
            $options->toArray(),
        ));

        return new JsonResponse(new ImportObject($import));
    }
}
