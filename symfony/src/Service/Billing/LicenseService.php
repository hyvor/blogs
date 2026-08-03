<?php

namespace App\Service\Billing;

use App\Entity\Blog;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Psr\Cache\CacheItemPoolInterface;

class LicenseService
{

    public function __construct(
        private BillingInterface $billing,
        private CacheItemPoolInterface $cache,
    ) {}

    /**
     * @throws FailedToGetLicenseException
     */
    public function getLicenseForBlog(Blog $blog): ResolvedLicense
    {
        $organizationId = $blog->getOrganizationId();

        if ($organizationId === null) {
            throw new FailedToGetLicenseException('Blog has no organization ID');
        }

        try {
            return $this->billing->license($organizationId);
        } catch (CommsApiFailedException $e) {
            throw new FailedToGetLicenseException('Failed to get license for blog', previous: $e);
        }
    }

    /**
     * @throws FailedToGetLicenseException
     */
    public function getCachedLicenseForBlog(Blog $blog): ResolvedLicense
    {
        $organizationId = $blog->getOrganizationId();

        if ($organizationId === null) {
            throw new FailedToGetLicenseException('Blog has no organization ID');
        }

        $item = $this->cache->getItem($this->getLicenseCacheKey($organizationId));

        $cachedLicense = $item->get();

        if ($cachedLicense instanceof ResolvedLicense) {
            return $cachedLicense;
        }

        try {
            $license = $this->billing->license($organizationId);
        } catch (CommsApiFailedException $e) {
            throw new FailedToGetLicenseException('Failed to get license for blog', previous: $e);
        }

        $item->set($license);
        $item->expiresAfter(3600); // Cache for 1 hour
        $this->cache->save($item);

        return $license;
    }

    private function getLicenseCacheKey(int $organizationId): string
    {
        return 'license_' . $organizationId;
    }

    public function clearLicenseCache(int $organizationId): void
    {
        $this->cache->deleteItem($this->getLicenseCacheKey($organizationId));
    }


}
