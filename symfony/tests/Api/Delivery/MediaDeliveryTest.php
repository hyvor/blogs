<?php

namespace App\Tests\Api\Delivery;

use App\Api\Delivery\AppDeliveryController;
use App\Api\Delivery\CustomDomainController;
use App\Api\Delivery\SubdomainController;
use App\Entity\Blog;
use App\Service\Delivery\Processor\MediaProcessor;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\MediaFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Media is always served from the blog's subdomain URL ({subdomainUrl}/media/{name}) and never redirected
 */
#[CoversClass(SubdomainController::class)]
#[CoversClass(AppDeliveryController::class)]
#[CoversClass(CustomDomainController::class)]
#[CoversClass(MediaProcessor::class)]
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
        $blog = BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'media-blog', ...$blogAttrs]);
        MediaFactory::createOne([
            'blog' => $blog,
            'name' => $name,
            'original_name' => $name,
            'extension' => pathinfo($name, PATHINFO_EXTENSION),
        ]);
        $this->getService(Filesystem::class)->write('blog/' . $blog->getId() . '/' . $name, $content);
        return $blog;
    }

    private function onSubdomain(string $path, string $subdomain = 'media-blog'): Response
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');
        $this->setEnvVar('CADDY_ROUTER', 'subdomain');
        $this->client->request('GET', $path, [], [], ['HTTP_HOST' => $subdomain . '.hyvorblogs.io']);
        return $this->client->getResponse();
    }

    private function onCustomDomain(string $host, string $path): Response
    {
        $this->setEnvVar('CADDY_ROUTER', 'customdomain');
        $this->client->request('GET', $path, [], [], ['HTTP_HOST' => $host, 'HTTPS' => 'on']);
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

    // subdomain: https://{subdomain}.{deliveryDomain}/media/{fileName} ================

    public function test_serves_media_from_subdomain(): void
    {
        $this->createBlogWithMedia();

        $this->assertServesSvg($this->onSubdomain('/media/image.svg'));
    }

    public function test_resizes_and_converts_images(): void
    {
        $png = (string) file_get_contents(__DIR__ . '/../../Service/Delivery/PathMatcher/Default/Media/test.png');
        $this->createBlogWithMedia('photo.png', $png);

        $response = $this->onSubdomain('/media/photo.png/100w');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/webp', $response->headers->get('Content-Type'));
    }

    public function test_returns_404_for_unknown_media(): void
    {
        $this->createBlogWithMedia();

        $this->assertSame(404, $this->onSubdomain('/media/other.svg')->getStatusCode());
    }

    public function test_does_not_serve_media_of_another_blog(): void
    {
        $this->createBlogWithMedia();
        BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'other-blog']);

        $this->assertSame(404, $this->onSubdomain('/media/image.svg', 'other-blog')->getStatusCode());
    }

    public function test_does_not_redirect_media_to_custom_domain(): void
    {
        $blog = $this->createBlogWithMedia();
        // other paths redirect to the custom domain (see SubdomainTest)
        CustomDomainFactory::createActiveFor($blog, 'media-blog.com');

        $this->assertServesSvg($this->onSubdomain('/media/image.svg'));
    }

    public function test_does_not_redirect_media_to_hosting_url(): void
    {
        // other paths redirect to the hosting URL (see SubdomainTest)
        $this->createBlogWithMedia(blogAttrs: ['hosting_url' => 'https://example.com/blog']);

        $this->assertServesSvg($this->onSubdomain('/media/image.svg'));
    }

    // app subdirectory: https://{DOMAIN_APP}/blog/{subdomain}/media/{fileName} ================

    public function test_serves_media_from_app_subdirectory(): void
    {
        $this->setEnvVar('DELIVERY_URL', '');
        $this->createBlogWithMedia();

        $this->client->request('GET', '/blog/media-blog/media/image.svg');

        $this->assertServesSvg($this->client->getResponse());
    }

    public function test_does_not_redirect_media_on_app_subdirectory(): void
    {
        // a delivery URL, a custom domain, or a hosting URL redirect other paths (see BlogOnSubdirectoryTest)
        $blog = $this->createBlogWithMedia(blogAttrs: ['hosting_url' => 'https://example.com/blog']);
        CustomDomainFactory::createActiveFor($blog, 'media-blog.com');

        $this->client->request('GET', '/blog/media-blog/media/image.svg');
        $this->assertServesSvg($this->client->getResponse());
    }

    // custom domain (legacy URLs): https://{customDomain}/media/{fileName} ================

    public function test_serves_legacy_media_from_custom_domain(): void
    {
        $blog = $this->createBlogWithMedia();
        CustomDomainFactory::createActiveFor($blog, 'media-blog.com');

        $this->assertServesSvg($this->onCustomDomain('media-blog.com', '/media/image.svg'));
    }

}
