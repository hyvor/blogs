<?php

namespace App\Domains\Delivery\Twig;

use App\Exceptions\TrustedException;
use Illuminate\Http\Request;

class DataAPICaller
{
    public function callApi(string $subdomain, string $endpoint, $query = [])
    {
        $domain = config('blogs.domain_app');
        $endpoint = trim($endpoint, '/');

        $request = Request::create(
            "https://$domain/api/data/v0/$subdomain/$endpoint",
            'GET',
            $query
        );

        $response = app()->handle($request);
        $data = $response->getContent();

        if ($response->isSuccessful()) {
            return json_decode($data);
        } else {
            $error = json_decode($data)->error ?? 'Something went wrong';

            throw new TrustedException($error);
        }
    }
}
