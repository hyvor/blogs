<?php

namespace App\Service\Billing;

use App\Service\Blog\BlogService;
use App\Service\Cache\BlogCacheService;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\License\LicenseChanged;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class LicenseChangedListener
{

    public function __construct(
        private LicenseService $licenseService,
        private BlogCacheService $blogCacheService,
        private BlogService $blogService
    ) {}

    /**
     * when a blog's license is changed, we have to clear the license cache and template cache
     * so that we show the branding correctly
     */
    public function __invoke(LicenseChanged $event): void
    {
        $oldLicense = $event->getPreviousLicense()->license;
        $newLicense = $event->getNewLicense()->license;

        $oldBranding = $oldLicense instanceof BlogsLicense ? $oldLicense->noBranding : null;
        $newBranding = $newLicense instanceof BlogsLicense ? $newLicense->noBranding : null;

        if ($oldBranding !== $newBranding) {
            $this->licenseService->clearLicenseCache($event->getOrganizationId());
            foreach ($this->blogService->getBlogsByOrganizationId($event->getOrganizationId()) as $blog) {
                $this->blogCacheService->clearTemplateCache($blog);
            }
        }
    }

}
