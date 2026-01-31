<?php

namespace App\Domains\Billing;

use App\Models\Blog;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Illuminate\Support\Facades\Cache;

class LicenseService
{

    /**
     * @throws CommsApiFailedException
     */
    public static function getLicense(Blog $blog): ?BlogsLicense
    {
        $organizationId = $blog->organization_id;

        if (!$organizationId) {
            // this is a temp or a dev blog
            return BlogsLicense::trial();
        }

        $billing = app(Billing::class);
        $resolvedLicense = $billing->license($organizationId);

        /** @var ?BlogsLicense */
        return $resolvedLicense->license;
    }

    private static function getHasLicenseCacheKey(int $organizationId): string
    {
        return "has-license-org:$organizationId";
    }

    /**
     * Checks if the blog (user) has a license.
     *
     * - If the value is cached, we return it.
     * - If not, we get it from the internal API
     *      - if the user has a license, we cache the value for 48 hours so it gives user 48 hours to upgrade, in case of a payment failure.
     *      - if the user does not have a license, we only cache it for 30 seconds so that we can quickly check after the user purchases a license.
     * - If the internal API call fails, we return true (to prevent the blog from being blocked) and will not cache the value.
     */
    public static function hasLicenseCached(Blog $blog): bool
    {
        $organizationId = $blog->organization_id;

        if (!$organizationId) {
            return false;
        }

        $key = self::getHasLicenseCacheKey($organizationId);

        $value = Cache::get($key);

        if ($value !== null) {
            return (bool)$value;
        }

        try {
            $license = self::getLicense($blog);
            $hasLicense = $license !== null;
            $cachePeriodSeconds = $hasLicense ?
                60 * 60 * 48 :  // 48 hours when the user has a license
                30; // 30 seconds when the user does not have a license
            Cache::put($key, $hasLicense, $cachePeriodSeconds);

            return $hasLicense;
        } catch (CommsApiFailedException $e) {
            return true;
        }
    }

}
