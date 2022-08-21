<?php

namespace App\Domains\Integrations\Paddle;

use App\Exceptions\TrustedException;
use Http;

class PaddleApiCaller
{
    public static function call(string $endpoint, array $data): object
    {
        $domain = config('services.paddle.sandbox') ?
            'https://sandbox-vendors.paddle.com/api/2.0' :
            'https://vendors.paddle.com/api/2.0';
        $url = $domain . $endpoint;

        $data['vendor_id'] = config('services.paddle.vendor_id');
        $data['vendor_auth_code'] = config('services.paddle.vendor_auth_code');

        $response = Http::post($url, $data);

        if (!$response->successful()) {
            throw new TrustedException("Calling Paddle endpoint $endpoint failed (HTTP ERROR)");
        }

        $json = $response->json();

        if (!$json['success']) {
            throw new TrustedException("Calling Paddle endpoint $endpoint failed (PADDLE ERROR)");
        }

        return (object) ($json['response'] ?? []);
    }
}
