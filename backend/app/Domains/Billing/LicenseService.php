<?php

namespace App\Domains\Billing;

use App\Models\Blog;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\BlogsLicense;

class LicenseService
{

    public static function getLicense(Blog $blog): ?BlogsLicense
    {
        $userId = $blog->hyvor_user_id;

        if (!$userId) {
            // this is a temp or a dev blog
            return new BlogsLicense(
                noBranding: true
            );
        }

        $billing = app(Billing::class);

        /** @var ?BlogsLicense $license */
        $license = $billing->license($userId, $blog->id);

        return $license;
    }

}
