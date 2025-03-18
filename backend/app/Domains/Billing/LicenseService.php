<?php

namespace App\Domains\Billing;

use App\Models\Blog;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Illuminate\Support\Facades\Cache;

class LicenseService
{

    /**
     * @throws InternalApiCallFailedException
     */
    public static function getLicense(Blog $blog): ?BlogsLicense
    {
        $userId = $blog->hyvor_user_id;

        if (!$userId) {
            // this is a temp or a dev blog
            return new BlogsLicense();
        }

        $billing = app(Billing::class);

        /** @var ?BlogsLicense $license */
        $license = $billing->license($userId, $blog->id);

        return $license;
    }

    private static function getHasLicenseCacheKey(int $userId): string
    {
        return "has-license:$userId";
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
        $userId = $blog->hyvor_user_id;

        if (!$userId) {
            return false;
        }

        $key = self::getHasLicenseCacheKey($userId);

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
        } catch (InternalApiCallFailedException $e) {
            return true;
        }
    }

}
