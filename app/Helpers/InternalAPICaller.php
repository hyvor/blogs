<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class InternalAPICaller
{
    static function data(string $subdomain, string $endpoint, $query = [])
    {

        $domain = config('blogs.domain_app');

        $request = Request::create(
            "https://$domain/api/data/v0/blog/$subdomain/$endpoint",
            'GET',
            $query
        );

        try {
            $response = app()->handle($request);
            $data = $response->getContent();

            return json_decode($data);
        } catch (\Exception $e) {
            return null;
        }
    }

    static function delivery(string $subdomain, string $path, array $query)
    {

        $domain = config('blogs.domain_app');

        $request = Request::create(
            "https://$domain/api/delivery/v0/blog/$subdomain/$path",
            'GET',
            $query
        );

        try {
            $response = app()->handle($request);
            $data = $response->getContent();
            return json_decode($data);
        } catch (\Exception $e) {
            return null;
        }
    }
}
