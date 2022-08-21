<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Domains\Delivery\DeliveryService;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class DomainDeliveryController extends Controller
{
    public function handle(Request $request, Blog $blog)
    {

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
