<?php

namespace App\Tests\Api\Delivery;

use App\Api\Delivery\SubdomainController;
use App\Entity\Enum\BlogHostingAt;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(SubdomainController::class)]
class SubdomainTest extends ApiTestCase
{

    private function call(
        string $host,
        string $path
    ): Response
    {
        $this->client->request('GET', $path, [], [], [
            'HTTP_HOST' => $host,
            'HTTP_X_ROUTER' => 'subdomain'
        ]);

        return $this->client->getResponse();
    }

    public function test_fails_when_delivery_domain_not_configured(): void
    {
        $this->setEnvVar('DELIVERY_URL', '');

        $response = $this->call('test.example.com', '/');
        $this->assertResponseStatusCodeSame(500);
        $this->assertStringContainsString('Delivery domain is not configured.', $response->getContent());
    }

    public function test_fails_when_subdomain_cannot_be_determined(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://example.com');

        $response = $this->call('example.com', '/');
        $this->assertResponseStatusCodeSame(500);
        $this->assertStringContainsString('Unable to determine subdomain from host: example.com and delivery domain: example.com', $response->getContent());
    }

    public function test_when_blog_not_found(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        $response = $this->call('nonexistent.hyvorblogs.io', '/');
        $this->assertResponseStatusCodeSame(404);
        $this->assertStringContainsString('Blog not found for subdomain: nonexistent', $response->getContent());
    }

    public function test_successful_request(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        $blog = BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'testblog', 'hosting_at' => BlogHostingAt::SUBDOMAIN]);
        RouteFactory::createDefaultsFor($blog);
        ThemeFileFactory::createIndexTwig($blog, '<h1>Hello World</h1>');

        $response = $this->call('testblog.hyvorblogs.io', '/');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('<h1>Hello World</h1>', $response->getContent());
    }

    public function test_redirects_if_not_hosted_at_subdomain(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        $blog = BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'testblog', 'hosting_at' => BlogHostingAt::SELF, 'hosting_url' => 'https://customdomain.com']);

        $response = $this->call('testblog.hyvorblogs.io', '/test');
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringContainsString('https://customdomain.com/test', $response->headers->get('Location'));
    }

    public function test_does_not_redirect_if_its_disabled(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        $blog = BlogFactory::createOneWithPrimaryLanguage([
            'subdomain' => 'testblog',
            'hosting_at' => BlogHostingAt::SELF,
            'hosting_url' => 'https://customdomain.com',
            'hosting_redirect_subdomain' => false
        ]);
        RouteFactory::createDefaultsFor($blog);
        ThemeFileFactory::createIndexTwig($blog, '<h1>Hello World</h1>');

        $response = $this->call('testblog.hyvorblogs.io', '/');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('<h1>Hello World</h1>', $response->getContent());
    }

}
