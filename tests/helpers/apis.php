<?php

use App\Models\Blog;
use Illuminate\Testing\TestResponse;

function dataApi(Blog|string $subdomain, string $endpoint, array $data = []): TestResponse {

    if ($subdomain instanceof Blog) {
        $subdomain = $subdomain->subdomain;
    }

    $endpoint = trim($endpoint, '/');

    return test()->call('GET', URL::to("/api/data/v0/$subdomain/$endpoint"), $data);

}