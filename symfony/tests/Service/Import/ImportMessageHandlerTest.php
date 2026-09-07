<?php

namespace App\Tests\Service\Import;

use App\Entity\Enum\ImportType;
use App\Entity\Enum\JobStatus;
use App\Entity\Post;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportMessage;
use App\Service\Import\MessageHandler\ImportMessageHandler;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(ImportMessageHandler::class)]
#[CoversClass(ImportService::class)]
class ImportMessageHandlerTest extends ApiTestCase
{
    public function test_imports_posts_from_sitemap(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'import-handler']);
        LanguageFactory::createOnePrimaryFor($blog);

        $sitemapXml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
                <url><loc>https://example.com/blog/hello-world</loc></url>
            </urlset>
            XML;

        $postHtml = <<<HTML
            <html>
                <head><title>Hello World</title></head>
                <body><article><p>Hello content</p></article></body>
            </html>
            HTML;

        $mockClient = new MockHttpClient(function (string $method, string $url) use ($sitemapXml, $postHtml) {
            if (str_contains($url, 'sitemap.xml')) {
                return new MockResponse($sitemapXml, ['response_headers' => ['Content-Type' => 'application/xml']]);
            }
            return new MockResponse($postHtml);
        });
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $importService = $this->getService(ImportService::class);
        $import = $importService->createImport($blog, ImportType::SITEMAP, 'https://example.com/sitemap.xml', [
            'sitemap_url' => 'https://example.com/sitemap.xml',
            'import_images' => false,
            'scraper_options' => ['contentSelector' => 'article'],
        ]);

        $message = new ImportMessage(
            $import->getId(),
            'https://example.com/sitemap.xml',
            false,
            ['contentSelector' => 'article'],
        );

        $this->getService(ImportMessageHandler::class)($message);

        $this->getEm()->refresh($import);
        $this->assertSame(JobStatus::COMPLETED, $import->getStatus());
        $this->assertSame(1, $import->getPostsCount());

        $posts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $posts);

        $variant = $posts[0]->getVariants()->first();
        $this->assertNotFalse($variant);
        $this->assertSame('hello-world', $variant->getSlug());
        $this->assertSame('Hello World', $variant->getTitle());
    }

    public function test_marks_import_failed_on_parser_error(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'import-handler-fail']);
        LanguageFactory::createOnePrimaryFor($blog);

        static::getContainer()->set(HttpClientInterface::class, new MockHttpClient(new MockResponse('', ['http_code' => 500])));

        $importService = $this->getService(ImportService::class);
        $import = $importService->createImport($blog, ImportType::SITEMAP, 'https://example.com/sitemap.xml');

        $message = new ImportMessage(
            $import->getId(),
            'https://example.com/sitemap.xml',
            false,
            ['contentSelector' => 'article'],
        );

        $this->getService(ImportMessageHandler::class)($message);

        $this->getEm()->refresh($import);
        $this->assertSame(JobStatus::FAILED, $import->getStatus());
        $this->assertSame('Cannot fetch sitemap', $import->getError());
    }
}
