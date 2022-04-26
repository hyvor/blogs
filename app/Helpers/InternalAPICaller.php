<?php

namespace App\Helpers;

use App\Exceptions\TrustedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class InternalAPICaller
{
    public static function data(string $subdomain, string $endpoint, $query = [])
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

    public static function delivery(string $subdomain, string $path, array $query)
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
            dd($e);
        }
    }
}
