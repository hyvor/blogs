<?php

namespace App\Http\Controllers\Subdomain;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\Delivery\DeliveryRepository;
use App\Helpers\InternalAPICaller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Blog;

class SubdomainController extends Controller
{

    public function handle(Request $request, Blog $blog)
    {
        $path = $request->route('path') ?? '';
        $data = DeliveryRepository::getResponseObject($blog, $path);

        return DeliveryRepository::getLaravelResponse($data);
    }

}
