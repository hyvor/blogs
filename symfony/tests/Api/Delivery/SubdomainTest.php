<?php

namespace App\Tests\Api\Delivery;

use App\Api\Delivery\SubdomainController;
use App\Entity\Enum\BlogHostingAt;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\ThemeFileFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(SubdomainController::class)]
class SubdomainTest extends ApiTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        // by default, blogs have a valid license
        $this->billing()->setLicenses(
            /** @param int[] $ids */
            fn(array $ids) => array_fill_keys(
                $ids,
                new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, BlogsLicense::trial())
            )
        );
    }

    private function billing(): BillingFake
    {
        return $this->getService(BillingFake::class);
    }

    private function call(
        string $host,
        string $path
    ): Response
    {
        $this->setEnvVar('CADDY_ROUTER', 'subdomain');
        $this->client->request('GET', $path, [], [], [
            'HTTP_HOST' => $host,
        ]);

        return $this->client->getResponse();
    }

    public function test_fails_when_delivery_domain_not_configured(): void
    {
        $this->setEnvVar('DELIVERY_URL', '');

        $response = $this->call('test.example.com', '/');
        $this->assertResponseStatusCodeSame(500);
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('Delivery domain is not configured.', $content);
    }

    public function test_fails_when_subdomain_cannot_be_determined(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://example.com');

        $response = $this->call('example.com', '/');
        $this->assertResponseStatusCodeSame(500);
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('Unable to determine subdomain from host: example.com and delivery domain: example.com', $content);
    }

    public function test_when_blog_not_found(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        $this->call('nonexistent.hyvorblogs.io', '/some/path');
        $this->assertResponseRedirects(
            'https://hyvor.com/blogs?via=subdomain&host=nonexistent.hyvorblogs.io&status=notfound',
            302
        );
    }

    public function test_when_blog_deleted(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        $blog = BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'testblog', 'hosting_at' => BlogHostingAt::SUBDOMAIN, 'deleted_at' => new \DateTimeImmutable()]);
        RouteFactory::createDefaultsFor($blog);
        ThemeFileFactory::createIndexTwig($blog, '<h1>Hello World</h1>');

        $this->call('testblog.hyvorblogs.io', '/');
        $this->assertResponseRedirects(
            'https://hyvor.com/blogs?via=subdomain&host=testblog.hyvorblogs.io&status=deleted',
            302
        );
    }

    public function test_when_blog_blocked(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'testblog', 'hosting_at' => BlogHostingAt::SUBDOMAIN, 'blocked_at' => new \DateTimeImmutable()]);

        $this->call('testblog.hyvorblogs.io', '/');
        $this->assertResponseRedirects(
            'https://hyvor.com/blogs?via=subdomain&host=testblog.hyvorblogs.io&status=blocked',
            302
        );
    }

    public function test_when_license_expired(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'testblog', 'hosting_at' => BlogHostingAt::SUBDOMAIN, 'organization_id' => 1]);
        $this->billing()->setLicenses([
            1 => new ResolvedLicense(ResolvedLicenseType::EXPIRED),
        ]);

        $this->call('testblog.hyvorblogs.io', '/');
        $this->assertResponseRedirects(
            'https://hyvor.com/blogs?via=subdomain&host=testblog.hyvorblogs.io&status=license_expired',
            302
        );
    }

    public function test_successful_request(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        $blog = BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'testblog', 'hosting_at' => BlogHostingAt::SUBDOMAIN]);
        RouteFactory::createDefaultsFor($blog);
        ThemeFileFactory::createIndexTwig($blog, '<h1>Hello World</h1>');

        $response = $this->call('testblog.hyvorblogs.io', '/');
        $this->assertResponseIsSuccessful();
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('<h1>Hello World</h1>', $content);
    }

    public function test_redirects_if_not_hosted_at_subdomain(): void
    {
        $this->setEnvVar('DELIVERY_URL', 'https://hyvorblogs.io');

        $blog = BlogFactory::createOneWithPrimaryLanguage(['subdomain' => 'testblog', 'hosting_at' => BlogHostingAt::SELF, 'hosting_url' => 'https://customdomain.com']);

        $response = $this->call('testblog.hyvorblogs.io', '/test');
        $this->assertResponseStatusCodeSame(302);
        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringContainsString('https://customdomain.com/test', $location);
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
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('<h1>Hello World</h1>', $content);
    }

}
