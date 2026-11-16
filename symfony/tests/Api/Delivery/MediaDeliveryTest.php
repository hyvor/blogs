<?php

namespace App\Tests\Api\Delivery;

use App\Api\Delivery\AppDeliveryController;
use App\Api\Delivery\MediaDomainController;
use App\Entity\Blog;
use App\Service\Delivery\BlogHomepageRedirector;
use App\Service\Delivery\DeliveryService;
use App\Service\Delivery\MediaDeliveryService;
use App\Service\Media\BlogIdEncoder;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\MediaFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(AppDeliveryController::class)]
#[CoversClass(MediaDomainController::class)]
#[CoversClass(MediaDeliveryService::class)]
#[CoversClass(DeliveryService::class)]
#[CoversClass(BlogHomepageRedirector::class)]
class MediaDeliveryTest extends ApiTestCase
{

    private const string SVG = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';

    protected function setUp(): void
    {
        parent::setUp();
        // by default, blogs have a valid license
        $this->getService(BillingFake::class)->setLicenses(
            /** @param int[] $ids */
            fn(array $ids) => array_fill_keys(
                $ids,
                new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, BlogsLicense::trial())
            )
        );
    }

    /**
     * @param array<string, mixed> $blogAttrs
     */
    private function createBlogWithMedia(
        string $name = 'image.svg',
        string $content = self::SVG,
        array $blogAttrs = [],
    ): Blog
    {
        $blog = BlogFactory::createOne($blogAttrs);
        MediaFactory::createOne([
            'blog' => $blog,
            'name' => $name,
            'original_name' => $name,
            'extension' => pathinfo($name, PATHINFO_EXTENSION),
        ]);
        $this->getService(Filesystem::class)->write('blog/' . $blog->getId() . '/' . $name, $content);
        return $blog;
    }

    private function onMediaDomain(string $path): Response
    {
        $this->setEnvVar('MEDIA_DOMAIN', 'media.hyvorblogs.io');
        $this->setEnvVar('CADDY_ROUTER', 'media');
        $this->client->request('GET', $path, [], [], ['HTTP_HOST' => 'media.hyvorblogs.io']);
        return $this->client->getResponse();
    }

    private function assertServesSvg(Response $response): void
    {
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(self::SVG, $response->getContent());
        $this->assertSame('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertStringContainsString('max-age=31536000', (string) $response->headers->get('Cache-Control'));
    }

    // app domain: /media/{blogIdEncoded}/{fileName} ================

    public function test_serves_media_from_app_domain(): void
    {
        $blog = $this->createBlogWithMedia();

        $this->client->request('GET', '/media/' . BlogIdEncoder::encode($blog->getId()) . '/image.svg');

        $this->assertServesSvg($this->client->getResponse());
    }

    public function test_resizes_and_converts_images(): void
    {
        $png = (string) file_get_contents(__DIR__ . '/../../Service/Delivery/PathMatcher/Default/Media/test.png');
        $blog = $this->createBlogWithMedia('photo.png', $png);

        $this->client->request('GET', '/media/' . BlogIdEncoder::encode($blog->getId()) . '/photo.png/100w');

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/webp', $response->headers->get('Content-Type'));
    }

    public function test_redirects_to_media_domain_when_configured(): void
    {
        $this->setEnvVar('MEDIA_DOMAIN', 'media.hyvorblogs.io');

        $this->client->request('GET', '/media/AQ/image.png/500w');

        $this->assertResponseRedirects('https://media.hyvorblogs.io/AQ/image.png/500w', 302);
    }

    public function test_returns_404_for_invalid_blog_id(): void
    {
        $this->createBlogWithMedia();

        $this->client->request('GET', '/media/not.valid/image.svg');
        $this->assertResponseStatusCodeSame(404);

        // non-canonical encoding of 1
        $this->client->request('GET', '/media/AR/image.svg');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_returns_404_for_unknown_blog(): void
    {
        $blog = $this->createBlogWithMedia();

        $this->client->request('GET', '/media/' . BlogIdEncoder::encode($blog->getId() + 1000) . '/image.svg');

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_returns_404_for_unknown_media(): void
    {
        $blog = $this->createBlogWithMedia();

        $this->client->request('GET', '/media/' . BlogIdEncoder::encode($blog->getId()) . '/other.svg');

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_does_not_serve_media_of_another_blog(): void
    {
        $this->createBlogWithMedia();
        $otherBlog = BlogFactory::createOne();

        $this->client->request('GET', '/media/' . BlogIdEncoder::encode($otherBlog->getId()) . '/image.svg');

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_returns_404_for_deleted_blog(): void
    {
        $blog = $this->createBlogWithMedia(blogAttrs: ['deleted_at' => new \DateTimeImmutable()]);

        $this->client->request('GET', '/media/' . BlogIdEncoder::encode($blog->getId()) . '/image.svg');

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_returns_404_for_blocked_blog(): void
    {
        $blog = $this->createBlogWithMedia(blogAttrs: ['blocked_at' => new \DateTimeImmutable()]);

        $this->client->request('GET', '/media/' . BlogIdEncoder::encode($blog->getId()) . '/image.svg');

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_returns_404_when_license_expired(): void
    {
        $blog = $this->createBlogWithMedia(blogAttrs: ['organization_id' => 1]);
        $this->getService(BillingFake::class)->setLicenses([
            1 => new ResolvedLicense(ResolvedLicenseType::EXPIRED),
        ]);

        $this->client->request('GET', '/media/' . BlogIdEncoder::encode($blog->getId()) . '/image.svg');

        $this->assertResponseStatusCodeSame(404);
    }

    // media domain: {MEDIA_DOMAIN}/{blogIdEncoded}/{fileName} ================

    public function test_serves_media_from_media_domain(): void
    {
        $blog = $this->createBlogWithMedia();

        $response = $this->onMediaDomain('/' . BlogIdEncoder::encode($blog->getId()) . '/image.svg');

        $this->assertServesSvg($response);
    }

    public function test_media_domain_returns_404_for_unknown_media(): void
    {
        $blog = $this->createBlogWithMedia();

        $this->assertSame(404, $this->onMediaDomain('/' . BlogIdEncoder::encode($blog->getId()) . '/other.svg')->getStatusCode());
        $this->assertSame(404, $this->onMediaDomain('/media/' . BlogIdEncoder::encode($blog->getId()) . '/image.svg')->getStatusCode());
        $this->assertSame(404, $this->onMediaDomain('/')->getStatusCode());
    }

    public function test_media_domain_does_not_serve_app_routes(): void
    {
        $this->onMediaDomain('/api/health');
        $this->assertResponseStatusCodeSame(404);
    }

}
