<?php
namespace App\Domains\Media;

use App\Data\Objects\ConsoleAPI\MediaObject;

class UnsplashRepository {

    public static function search(string $search, $page = 1) {

        \Unsplash\HttpClient::init([
            'applicationId'	=> config('services.unsplash.access_key'),
            'secret'	=> config('services.unsplash.secret_key'),
            'utmSource' => 'Hyvor Blogs'
        ]);

        $limit = 30;
        $response = \Unsplash\Search::photos($search, $page, $limit);

        return collect($response->getResults());
    }

}