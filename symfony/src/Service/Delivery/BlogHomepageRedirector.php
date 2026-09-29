<?php

namespace App\Service\Delivery;

use App\Entity\Blog;
use App\Service\AppConfig;
use App\Service\Billing\FailedToGetLicenseException;
use App\Service\Billing\LicenseService;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Redirects visitors of unavailable blogs (subdomain / custom domain)
 * to the homepage: hyvor.com/blogs on cloud, the app domain on self-hosted.
 */
class BlogHomepageRedirector
{

    public function __construct(
        private AppConfig $appConfig,
        private InternalConfig $internalConfig,
        private LicenseService $licenseService,
    ) {}

    /**
     * @param 'subdomain'|'custom_domain' $via
     */
    public function redirect(string $via, string $host, string $status): RedirectResponse
    {
        if ($this->internalConfig->getDeployment()->isCloud()) {
            $base = rtrim($this->internalConfig->getInstance(), '/') . '/blogs';
        } else {
            $base = $this->appConfig->getTlsMode()->getScheme() . '://' . $this->appConfig->getDomainApp() . '/';
        }

        return new RedirectResponse(
            $base . '?' . http_build_query(['via' => $via, 'host' => $host, 'status' => $status]),
            302
        );
    }

    /**
     * Returns a redirect if the (existing) blog should not be served, null otherwise.
     *
     * @param 'subdomain'|'custom_domain' $via
     */
    public function redirectIfUnavailable(Blog $blog, string $via, string $host): ?RedirectResponse
    {
        if ($blog->getDeletedAt()) {
            return $this->redirect($via, $host, 'deleted');
        }

        if ($blog->getBlockedAt() !== null) {
            return $this->redirect($via, $host, 'blocked');
        }

        if ($this->internalConfig->getDeployment()->isCloud()) {
            try {
                $license = $this->licenseService->getCachedLicenseForBlog($blog);
                if ($license->license === null) {
                    return $this->redirect($via, $host, 'license_expired');
                }
            } catch (FailedToGetLicenseException) {
                // fail open: do not take blogs down when the license cannot be resolved
            }
        }

        return null;
    }

}
