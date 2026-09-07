<?php

namespace App\Tests\Api\Delivery;

use App\Api\Delivery\CustomDomainController;
use App\Entity\Enum\BlogHostingAt;
use App\Service\Hosting\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(CustomDomainController::class)]
#[CoversClass(CustomDomainService::class)]
class CustomDomainTest extends ApiTestCase
{

    private function call(
        string $host,
        string $path,
        bool $https = true
    ): Response
    {
        $this->setEnvVar('CADDY_ROUTER', 'customdomain');
        $this->client->request('GET', $path, [], [], [
            'HTTP_HOST' => $host,
            'HTTPS' => $https ? 'on' : null,
        ]);

        return $this->client->getResponse();
    }

    public function test_redirects_when_blog_not_found(): void
    {
        $response = $this->call('nonexistent.customdomain.com', '/some/path');

        $this->assertResponseRedirects(
            'https://blogs.hyvor.com/?via=custom_domain&host=nonexistent.customdomain.com',
            302
        );
    }

    public function test_redirects_when_blog_deleted(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage([
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
            'deleted_at' => new \DateTimeImmutable(),
        ]);
        $customDomain = CustomDomainFactory::createActiveFor($blog, 'deleted.customdomain.com');
        $blog->setCustomDomain($customDomain);
        $this->getEm()->flush();

        $response = $this->call('deleted.customdomain.com', '/some/path');

        $this->assertResponseRedirects(
            'https://blogs.hyvor.com/?via=custom_domain&host=deleted.customdomain.com',
            302
        );
    }

    public function test_returns_response_when_blog_found(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage([
            'hosting_at' => BlogHostingAt::SUBDOMAIN,
        ]);
        $customDomain = CustomDomainFactory::createActiveFor($blog, 'ishini.io');
        $blog->setCustomDomain($customDomain);
        $this->getEm()->flush();
        RouteFactory::createDefaultsFor($blog);
        ThemeFileFactory::createIndexTwig($blog, '<h1>Hello World</h1>');

        $response = $this->call('ishini.io', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('<h1>Hello World</h1>', $content);
    }

    public function test_for_unknown_acme_token(): void
    {
        $blog = BlogFactory::createOne();
        $customDomain = CustomDomainFactory::createActiveFor($blog, 'supun.io');
        $blog->setCustomDomain($customDomain);
        $this->getEm()->flush();

        $response = $this->call('supun.io', '/.well-known/acme-challenge/unknown-token', https: false);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function test_for_known_acme_token(): void
    {
        $token = 'test-token-123';
        $keyAuth = 'test-key-auth-value';
        $cache = $this->getService(CacheItemPoolInterface::class);

        $cacheItem = $cache->getItem('acme_challenge_' . $token);
        $cacheItem->set($keyAuth);
        $cache->save($cacheItem);

        $response = $this->call('some.customdomain.com', '/.well-known/acme-challenge/' . $token, https: false);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        $this->assertSame($keyAuth, $response->getContent());
    }


}
