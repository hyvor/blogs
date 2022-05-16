<?php

namespace App\Http\Controllers\Subdomain;

use App\Domains\Delivery\DeliveryRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class SubdomainController extends Controller
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
        $data = DeliveryRepository::getResponseObject($blog, $path);



        return DeliveryRepository::getLaravelResponse($data);
    }
}
