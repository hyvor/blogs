<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Data\Objects\DeliveryAPI\DeliveryAPIResponseObject;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Domains\Delivery\DeliveryRepository;

class DeliveryAPIController
{
    public function handle(Request $request, Blog $blog)
    {
        /**
         * Delivery API says "how to serve a path"
         *
         * Takes two inputs:
         *  subdomain
         *  path
         *
         * Returns an output as specified [here]()
         */

        $response = DeliveryRepository::getResponseObject(
            $blog,
            $request->input('path', ''),
        );

        /**
         * Base-64 encode
         */
        if (isset($response->content)) {
            $response->content = base64_encode($response->content);
        }

        return response()->json($response);
    }

    private static function notFound()
    {
        return response()->json(DeliveryAPIResponseObject::forFile('404', 'text/html', 404));
    }
}
