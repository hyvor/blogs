<?php

use App\Models\Blog;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

if (!function_exists('consoleApi')) {
    function consoleApi(Blog|string $blog, string $method, string $endpoint, $data = []): TestResponse
    {
        $endpoint = trim($endpoint, '/');
        $subdomain = $blog instanceof Blog ? $blog->subdomain : $blog;
        return test()->call($method, URL::to("/api/console/v0/blog/$subdomain/$endpoint"), $data);
    }
}

if (!function_exists('consoleUserApi')) {
    function consoleUserApi(string $method, string $endpoint, $data = []): TestResponse
    {
        return test()->call($method, URL::to('/api/console/v0' . $endpoint), $data);
    }
}

if (!function_exists('dataApi')) {
    function dataApi(Blog|string $subdomain, string $endpoint, array $data = []): TestResponse
    {

        if ($subdomain instanceof Blog) {
            $subdomain = $subdomain->subdomain;
        }

        $endpoint = trim($endpoint, '/');

        return test()->call('GET', URL::to("/api/data/v0/$subdomain/$endpoint"), $data);

    }
}

if (!function_exists('integrationApi')) {
    function integrationApi(string $method, string $endpoint, $data = []): TestResponse
    {
        $endpoint = trim($endpoint, '/');
        return test()->call($method, config('app.url') . "/integrations/$endpoint", $data);
    }
}