<?php

namespace Tests\Feature\ConsoleAPI\ApiUser;

use App\Models\Blog;

it('checks for subdomain', function () {
    consoleUserApi('GET', '/blog/check-subdomain', [
        'subdomain' => 'something',
    ])->assertOk()
        ->assertJson([
            'available' => true,
        ]);
});

it('returns error for taken subdomain', function () {
    $blog = blog();
    consoleUserApi('GET', '/blog/check-subdomain', [
        'subdomain' => $blog->subdomain,
    ])->assertOk()
        ->assertJson([
            'available' => false,
        ]);
});
