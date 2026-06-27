<?php

namespace App\Tests\Api\Console\Blog\Import;

use App\Api\Console\Controller\ImportSitemapController;
use App\Entity\Enum\ImportType;
use App\Entity\Enum\JobStatus;
use App\Message\ImportMessage;
use App\Service\Import\ImportService;
use App\Service\Import\Sitemap\PageScraper\PageScraper;
use App\Service\Import\Sitemap\SitemapParser;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(ImportSitemapController::class)]
#[CoversClass(ImportService::class)]
#[CoversClass(PageScraper::class)]
#[CoversClass(SitemapParser::class)]
#[CoversClass(ImportMessage::class)]
class ImportSitemapTest extends ApiTestCase
{
    public function test_test_works(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'import-sitemap-test']);

        $html = <<<HTML
            <html>
                <meta property="og:image" content="https://example.com/image.png" />
                <h1>Post Title</h1>
                <p class="description">My description</p>
                <article>
                    <p>This is a test post</p>
                    <blockquote>
                        Rejected
                    </blockquote>
                </article>
                <time>2023-04-05</time>
            </html>
            HTML;

        static::getContainer()->set(HttpClientInterface::class, new MockHttpClient(new MockResponse($html)));

        $this->consoleBlogApi('POST', $blog, '/data/import/sitemap/test', [
            'url' => 'https://example.com/blog/post',
            'slug_exclude' => '/blog',
            'css' => [
                'title' => 'h1',
                'description' => 'p.description',
                'content' => 'article',
                'content_exclude' => 'blockquote',
                'published_date' => 'time',
            ],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $this->assertSame('Post Title', $json['data']['title']);
        $this->assertSame('My description', $json['data']['description']);
        $this->assertSame('<p>This is a test post</p>', $json['data']['content_html']);
        $this->assertSame((new \DateTimeImmutable('2023-04-05'))->getTimestamp(), $json['data']['published_at']);
        $this->assertSame('https://example.com/image.png', $json['data']['featured_image_url']);
        $this->assertSame('post', $json['data']['slug']);
        $this->assertSame('css_selector', $json['meta']['select_type']['title']);
        $this->assertSame('css_selector', $json['meta']['select_type']['description']);
        $this->assertSame('css_selector', $json['meta']['select_type']['published_at']);
    }

    public function test_test_handles_http_errors(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'import-sitemap-404']);

        static::getContainer()->set(HttpClientInterface::class, new MockHttpClient(new MockResponse('', ['http_code' => 404])));

        $this->consoleBlogApi('POST', $blog, '/data/import/sitemap/test', [
            'url' => 'https://example.com/blog/post',
            'css' => ['content' => 'article'],
        ], user: $user);

        $this->assertResponseFailed(422, 'cannot_fetch');
    }

    public function test_test_handles_content_errors(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'import-sitemap-empty']);

        static::getContainer()->set(HttpClientInterface::class, new MockHttpClient(new MockResponse('<html></html>')));

        $this->consoleBlogApi('POST', $blog, '/data/import/sitemap/test', [
            'url' => 'https://example.com/blog/post',
            'css' => ['content' => 'article'],
        ], user: $user);

        $this->assertResponseFailed(422, 'cannot_get_content');
    }

    public function test_import_sitemap_dispatches_message(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'import-sitemap-import']);

        $this->consoleBlogApi('POST', $blog, '/data/import/sitemap/import', [
            'sitemap_url' => 'https://example.com/sitemap.xml',
            'css' => ['content' => 'article'],
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('pending', $json['status']);

        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(ImportMessage::class, 1);

        /** @var ImportMessage $message */
        $message = $dispatched->first(ImportMessage::class)->getMessage();

        $this->assertSame('https://example.com/sitemap.xml', $message->sitemapUrl);
        $this->assertFalse($message->importImages);
        $this->assertSame('article', $message->scraperOptions['contentSelector']);

        $import = $this->getEm()->getRepository(\App\Entity\Import::class)->find($message->importId);
        $this->assertNotNull($import);
        $this->assertSame(ImportType::SITEMAP, $import->getType());
        $this->assertSame('https://example.com/sitemap.xml', $import->getName());
        $this->assertSame(JobStatus::PENDING, $import->getStatus());
    }

    public function test_does_not_import_when_a_pending_import_exists(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(['subdomain' => 'import-sitemap-pending']);

        $this->getService(ImportService::class)->createImport($blog, ImportType::SITEMAP, '');

        $this->consoleBlogApi('POST', $blog, '/data/import/sitemap/import', [
            'sitemap_url' => 'https://example.com/sitemap.xml',
            'css' => ['content' => 'article'],
        ], user: $user);

        $this->assertResponseFailed(422, 'There is already an import in progress for this blog.');
    }
}
