<?php

namespace App\Http\Middleware\App\Delivery;

use App\Data\Enums\ApiKeysTypeEnum;
use App\Domains\Api\ApiKeysRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;
use Illuminate\Http\Request;

class DeliveryApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->input('api_key');

        if (!$apiKey) {
            throw new TrustedException('API Key not set');
        }

        $blog = app(Blog::class);
        if (!ApiKeysRepository::hasKey($blog, ApiKeysTypeEnum::DELIVERY, $apiKey)) {
            throw new TrustedException('API Key invalid');
        }

        return $next($request);
    }
}
