<?php

namespace App\Domains\Media\Services;

use Illuminate\Support\Collection;
use Unsplash\HttpClient;
use Unsplash\Search;

class UnsplashService
{
    public function __construct()
    {
        HttpClient::init([
            'applicationId' => config('services.unsplash.access_key'),
            'secret' => config('services.unsplash.secret_key'),
            'utmSource' => 'Hyvor Blogs',
        ]);
    }

    public function search(string $search, int $page = 1): Collection
    {
        $limit = 30;
        $response = Search::photos($search, $page, $limit);

        return collect($response->getResults());
    }
}
