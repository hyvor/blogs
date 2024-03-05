<?php

namespace Tests\Unit\Domains\Delivery\Response\Fonts;

use App\Data\Enums\DeliveryAPICacheControlHeaderEnum;
use App\Data\Enums\DeliveryAPIFileTypeEnum;
use App\Data\Enums\DeliveryAPITypeEnum;
use App\Domains\Delivery\PathMatcher;
use Illuminate\Support\Facades\Http;

it('returns font file', function() {

    Http::fake([
        'https://fonts.bunny.net/*' => Http::response('empty response', 200, [
            'content-type' => 'application/font-woff2',
        ])
    ]);

    $blog = blog();

    $pathMatcher = new PathMatcher($blog, "/fonts/file/mulish/files/mulish-latin-400-normal.woff2");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('empty response');
    expect($responseObject->status)->toBe(200);
    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::ASSET);
    expect($responseObject->mime_type)->toBe('application/font-woff2');
    expect($responseObject->cache_control)->toBe(DeliveryAPICacheControlHeaderEnum::CACHE_ONE_YEAR);

});

it('on fail', function() {

    Http::fake([
        'https://fonts.bunny.net/*' => Http::response('', 500)
    ]);

    $blog = blog();

    $pathMatcher = new PathMatcher($blog, "/fonts/file/mulish/files/mulish-latin-400-normal.woff2");
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->content)->toBe('Failed to fetch font file: Request failed');
    expect($responseObject->status)->toBe(500);
    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::FILE);
    expect($responseObject->file_type)->toBe(DeliveryAPIFileTypeEnum::ASSET);
    expect($responseObject->mime_type)->toBe('text/plain');
    expect($responseObject->cache_control)->toBe(DeliveryAPICacheControlHeaderEnum::NO_CACHE);

});