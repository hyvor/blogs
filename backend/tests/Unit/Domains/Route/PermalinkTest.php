<?php

namespace Tests\Unit\Domains\Route;

use App\Domains\Route\PermalinkRepository;

it('gets blog url', function() {

    $blog = blog(['subdomain' => 'test']);

    config(['blogs.delivery_url' => 'https://hyvorblogs.io']);
    expect(PermalinkRepository::getBaseUrl($blog))->toBe('https://test.hyvorblogs.io');

    config(['blogs.delivery_url' => 'https://localhost:2211']);
    expect(PermalinkRepository::getBaseUrl($blog))->toBe('https://test.localhost:2211');

    config(['blogs.delivery_url' => 'http://userblogs.hyvorstaging.com']);
    expect(PermalinkRepository::getBaseUrl($blog))->toBe('http://test.userblogs.hyvorstaging.com');

});