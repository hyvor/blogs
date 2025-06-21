<?php

declare(strict_types=1);

namespace App\Http\Controllers\DeliveryAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Billing\LicenseService;
use App\Domains\Delivery\DeliveryService;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class DomainDeliveryController extends Controller
{
    public function handle(Request $request, Blog $blog): mixed
    {
        if ($blog->is_blocked) {
            return redirect('https://blogs.hyvor.com');
        }

        // check license
        if (
            $blog->type === BlogTypeEnum::DEFAULT &&
            LicenseService::hasLicenseCached($blog) === false
        ) {
            return redirect('https://blogs.hyvor.com', 302, [
                'X-Blog-Subdomain' => $blog->subdomain,
                'X-Redirect-Reason' => 'No license',
                'Cache-Control' => 'max-age=0, must-revalidate, no-cache, no-store, private',
            ]);
        }

        /**
         * We cannot use $request->path() because laravel has logic to remove trailing slash
         * We have to exactly know if there's a trailing slash or not
         * That's why getPathInfo() is used. It is a Symfony function and it does not trim trailing slash
         * And, it always has the leading /
         */
        $path = $request->getPathInfo();
        return DeliveryService::getLaravelResponse($blog, $path);
    }
}
