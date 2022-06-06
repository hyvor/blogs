<?php

namespace Tests\Feature\ConsoleAPI\ApiUser;

use App\Models\Blog;

it('checks for subdomain', function () {
    $this->callConsoleUserApi('GET', '/blog/check-subdomain', [
        'subdomain' => 'something',
    ])->assertOk();
});

it('returns error for taken subdomain', function () {
    $this->callConsoleUserApi('GET', '/blog/check-subdomain', [
        'subdomain' => Blog::first()->subdomain,
    ])->assertUnprocessable();
});
