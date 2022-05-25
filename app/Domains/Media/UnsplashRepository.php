<?php

namespace App\Domains\Media;

use Unsplash\HttpClient;
use Unsplash\Search;

class UnsplashRepository
{

    public static function search(string $search, $page = 1)
    {
        HttpClient::init([
            'applicationId' => config('services.unsplash.access_key'),
            'secret' => config('services.unsplash.secret_key'),
            'utmSource' => 'Hyvor Blogs',
        ]);

        $limit = 30;
        $response = Search::photos($search, $page, $limit);

        return collect($response->getResults());
    }
}
