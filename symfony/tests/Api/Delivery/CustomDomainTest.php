<?php

namespace App\Tests\Api\Delivery;

use App\Api\Delivery\CustomDomainController;
use App\Entity\Enum\BlogHostingAt;
use App\Service\CustomDomain\CustomDomainService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(CustomDomainController::class)]
#[CoversClass(CustomDomainService::class)]
class CustomDomainTest extends ApiTestCase
{

    private function call(
        string $host,
        string $path
    ): Response
    {
        $this->client->request('GET', $path, [], [], [
            'HTTP_HOST' => $host,
            'HTTP_X_ROUTER' => 'customdomain',
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
        $this->assertStringContainsString('<h1>Hello World</h1>', $response->getContent());
    }

}
