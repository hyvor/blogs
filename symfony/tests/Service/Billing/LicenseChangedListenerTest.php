<?php

namespace App\Tests\Service\Billing;

use App\Service\Billing\LicenseChangedListener;
use App\Service\Billing\LicenseService;
use App\Service\Cache\Event\CacheClearTemplatesEvent;
use App\Tests\Factory\BlogFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\License\LicenseChanged;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(LicenseChangedListener::class)]
class LicenseChangedListenerTest extends KernelTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->getService(CacheItemPoolInterface::class)->clear();
    }

    private function licenseService(): LicenseService
    {
        return $this->getService(LicenseService::class);
    }

    private function billing(): BillingFake
    {
        return $this->getService(BillingFake::class);
    }

    public function test_clears_caches_when_branding_changes(): void
    {
        $blog1 = BlogFactory::createOne(['organization_id' => 1]);
        $blog2 = BlogFactory::createOne(['organization_id' => 1]);

        $oldBlogsLicense = BlogsLicense::trial();
        $oldBlogsLicense->noBranding = false;
        $oldLicense = new ResolvedLicense(ResolvedLicenseType::TRIAL, $oldBlogsLicense);
        $this->billing()->setLicenses([1 => $oldLicense]);

        // seed the license cache
        $this->licenseService()->getCachedLicenseForBlog($blog1);

        $newBlogsLicense = BlogsLicense::trial();
        $newBlogsLicense->noBranding = true;
        $newLicense = new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $newBlogsLicense);
        $this->billing()->setLicenses([1 => $newLicense]);

        $this->getEd()->dispatch(new LicenseChanged(
            1,
            Component::BLOGS,
            $oldLicense,
            $newLicense
        ));

        // license cache was cleared, so the fresh (new) license is returned now
        $this->assertSame($newLicense, $this->licenseService()->getCachedLicenseForBlog($blog1));

        // template cache was cleared for every blog in the organization
        $this->getEd()->assertDispatchedCount(CacheClearTemplatesEvent::class, 2);
        $clearedBlogIds = array_values(array_map(
            fn(CacheClearTemplatesEvent $event) => $event->blog->getId(),
            array_filter(
                $this->getEd()->getDispatchedEvents(),
                fn($event) => $event instanceof CacheClearTemplatesEvent
            )
        ));
        $this->assertEqualsCanonicalizing([$blog1->getId(), $blog2->getId()], $clearedBlogIds);
    }

    public function test_does_not_clear_caches_when_branding_unchanged(): void
    {
        $blog = BlogFactory::createOne(['organization_id' => 1]);

        $oldBlogsLicense = BlogsLicense::trial();
        $oldBlogsLicense->noBranding = false;
        $oldLicense = new ResolvedLicense(ResolvedLicenseType::TRIAL, $oldBlogsLicense);
        $this->billing()->setLicenses([1 => $oldLicense]);

        // seed the license cache
        $cached = $this->licenseService()->getCachedLicenseForBlog($blog);

        $newBlogsLicense = BlogsLicense::trial();
        $newBlogsLicense->noBranding = false;
        $newLicense = new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, $newBlogsLicense);
        $this->billing()->setLicenses([1 => $newLicense]);

        $this->getEd()->dispatch(new LicenseChanged(
            1,
            Component::BLOGS,
            $oldLicense,
            $newLicense
        ));

        // license cache was NOT cleared, so the stale (old, TRIAL) cached license is still returned
        $result = $this->licenseService()->getCachedLicenseForBlog($blog);
        $this->assertEquals($cached, $result);
        $this->assertSame(ResolvedLicenseType::TRIAL, $result->type);

        $this->getEd()->assertNotDispatched(CacheClearTemplatesEvent::class);
    }

}
