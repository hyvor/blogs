<?php

namespace App\Http\Controllers\DeliveryAPI;

use App\Domains\Delivery\DeliveryService;
use App\Models\Blog;
use Illuminate\Http\Request;

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
        $response = DeliveryService::getResponseObject(
            $blog,
            $request->input('path') || '',
        );

        /**
         * Base-64 encode
         */
        if (isset($response->content)) {
            $response->content = base64_encode($response->content);
        }

        return response()->json($response);
    }
}
