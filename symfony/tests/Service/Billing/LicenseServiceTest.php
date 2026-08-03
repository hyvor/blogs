<?php

namespace App\Tests\Service\Billing;

use App\Service\Billing\FailedToGetLicenseException;
use App\Service\Billing\LicenseService;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(LicenseService::class)]
class LicenseServiceTest extends KernelTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->getService(CacheItemPoolInterface::class)->clear();
    }

    private function service(): LicenseService
    {
        return $this->getService(LicenseService::class);
    }

    // -----------------------------------------------------------------------
    // getLicenseForBlog
    // -----------------------------------------------------------------------

    public function test_gets_license_for_blog(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 1]);

        $license = new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial());
        $this->getService(BillingFake::class)->setLicenses([1 => $license]);

        $this->assertSame($license, $this->service()->getLicenseForBlog($blog));
    }

    public function test_throws_when_blog_has_no_organization_id(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => null]);

        $this->expectException(FailedToGetLicenseException::class);
        $this->service()->getLicenseForBlog($blog);
    }

    // -----------------------------------------------------------------------
    // getCachedLicenseForBlog
    // -----------------------------------------------------------------------

    public function test_gets_and_caches_license_for_blog(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 1]);

        $license = new ResolvedLicense(ResolvedLicenseType::TRIAL, BlogsLicense::trial());
        $this->getService(BillingFake::class)->setLicenses([1 => $license]);

        $this->assertSame($license, $this->service()->getCachedLicenseForBlog($blog));

        // change the underlying license; cached call should still return the old one
        $newLicense = new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, BlogsLicense::trial());
        $this->getService(BillingFake::class)->setLicenses([1 => $newLicense]);

        $this->assertEquals($license, $this->service()->getCachedLicenseForBlog($blog));
    }

    public function test_throws_when_blog_has_no_organization_id_for_cached_license(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => null]);

        $this->expectException(FailedToGetLicenseException::class);
        $this->service()->getCachedLicenseForBlog($blog);
    }

}
