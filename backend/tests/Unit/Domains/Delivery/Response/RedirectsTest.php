<?php

namespace Tests\Feature\DeliveryApi\PathMatcher;

use App\Data\Enums\DeliveryAPITypeEnum;
use App\Data\Enums\RedirectTypeEnum;
use App\Domains\Delivery\PathMatcher;
use App\Domains\Redirect\RedirectRepository;

it('matches redirect', function () {
    $from = '/redirect';
    $to = 'https://somewhere.com';

    $blog = blog();

    RedirectRepository::createRedirect(
        $blog,
        $from,
        $to,
        RedirectTypeEnum::PERMANENT
    );

    $pathMatcher = new PathMatcher($blog, $from);
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->type)->toBe(DeliveryAPITypeEnum::REDIRECT);
    expect($responseObject->to)->toBe($to);
    expect($responseObject->status)->toBe(301);
});

it('does not match if the redirect is not found', function () {
    $from = '/redirect/path';

    $blog = blogWithLanguage();

    $pathMatcher = new PathMatcher($blog, $from);
    $responseObject = $pathMatcher->getResponseObject();

    expect($responseObject->status)->toBe(404);
});
