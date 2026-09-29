<?php

namespace App\Tests\Service\Delivery;

use App\Service\Delivery\BlogHomepageRedirector;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Internal\Deployment;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogHomepageRedirector::class)]
class BlogHomepageRedirectorTest extends KernelTestCase
{

    private function expireLicense(): void
    {
        $billing = $this->getService(BillingFake::class);
        $billing->setLicenses([1 => new ResolvedLicense(ResolvedLicenseType::EXPIRED)]);
    }

    public function test_license_is_checked_on_cloud(): void
    {
        $this->setEnvVar('DEPLOYMENT', Deployment::CLOUD->value);
        $blog = BlogFactory::createOne(['organization_id' => 1]);
        $this->expireLicense();

        $response = $this->getService(BlogHomepageRedirector::class)
            ->redirectIfUnavailable($blog, 'subdomain', 'test.example.com');

        $this->assertNotNull($response);
        $this->assertStringContainsString('status=license_expired', $response->getTargetUrl());
    }

    public function test_license_is_not_checked_on_prem(): void
    {
        $this->setEnvVar('DEPLOYMENT', Deployment::ON_PREM->value);
        $blog = BlogFactory::createOne(['organization_id' => 1]);
        $this->expireLicense();

        $response = $this->getService(BlogHomepageRedirector::class)
            ->redirectIfUnavailable($blog, 'subdomain', 'test.example.com');

        $this->assertNull($response);
    }
}
