<?php

namespace Tests\Unit\Domains\Delivery\Response\Fonts;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\Delivery\PathMatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('returns font css', function () {
    Cache::clear();

    $response = "@font-face {
  font-family: 'Mulish';
  font-style: normal;
  font-weight: 400;
  font-stretch: 100%;
  src: url(https://fonts.bunny.net/mulish/files/mulish-latin-400-normal.woff2) format('woff2'), url(https://fonts.bunny.net/mulish/files/mulish-latin-400-normal.woff) format('woff'); 
  unicode-range: U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0300-0301,U+0303-0304,U+0308-0309,U+0323,U+0329,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;
}";

    Http::fake([
        'https://fonts.bunny.net/css*' => Http::response($response)
    ]);

    $blog = blog();
    $blogUrl = $blog->url();

    $replaced = "@font-face {
  font-family: 'Mulish';
  font-style: normal;
  font-weight: 400;
  font-stretch: 100%;
  src: url($blogUrl/fonts/file/mulish/files/mulish-latin-400-normal.woff2) format('woff2'), url($blogUrl/fonts/file/mulish/files/mulish-latin-400-normal.woff) format('woff'); 
  unicode-range: U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0300-0301,U+0303-0304,U+0308-0309,U+0323,U+0329,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;
}";

    $pathMatcher = new PathMatcher($blog, "/fonts/css/mulish:400");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe($replaced);
    expect($responseObject->status)->toBe(200);
    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::ASSET);
    expect($responseObject->mime_type)->toBe('text/css');
    expect($responseObject->cache_control)->toBe(DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR);

    Http::assertSent(function ($request) {
        expect($request->url())->toBe('https://fonts.bunny.net/css?family=mulish:400&display=swap');
        return true;
    });

    expect(Cache::has('bunny-fonts-https://fonts.bunny.net/css?family=mulish:400&display=swap'))->toBeTrue();
    $cachedValue = Cache::get('bunny-fonts-https://fonts.bunny.net/css?family=mulish:400&display=swap');
    expect($cachedValue)->toBe($replaced);
});

it('on fail', function () {
    Cache::clear();
    Http::fake([
        'https://fonts.bunny.net/css*' => Http::response('', 500)
    ]);

    $blog = blog();
    $pathMatcher = new PathMatcher($blog, "/fonts/css/mulish:400");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('Failed to fetch font css: Request failed');
    expect($responseObject->status)->toBe(500);
    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);

    expect(Cache::has('bunny-fonts-https://fonts.bunny.net/css?family=mulish:400&display=swap'))->toBeFalse();
});