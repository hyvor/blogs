<?php

namespace Tests\Feature\ConsoleAPI\ApiUser;

use App\Models\Blog;

it('it creates a temporary blog', function () {

    $data = consoleUserApi('GET', '/init-temp')
        ->assertOk()
        ->json();

    expect($data['blogs'])->toHaveCount(1);

    $blog = $data['blogs'][0];

    expect($blog['name'])->toBe('Temporary Blog');
    expect($blog['subdomain'])->toStartWith('temp-');
    expect($blog['type'])->toBe('temp');
    expect(Blog::find($blog['id'])->ip)->toBe('127.0.0.1');

    $user = $data['user'];

    expect($user['id'])->toBe(0);
    expect($user['name'])->toBe('Temp User');
    expect($user['username'])->toBeNull();
    expect($user['picture_url'])->toBeNull();
});

it('loads a temporary blog', function () {

    $blog = blog(['type' => 'temp']);

    $data = consoleUserApi('GET', '/init-temp', ['temp_subdomain' => $blog->subdomain])
        ->assertOk()
        ->json();

    expect($data['blogs'])->toHaveCount(1);
    expect($data['blogs'][0]['subdomain'])->toBe($blog->subdomain);
});

it('does not allow loading other blogs as temporary', function () {

    $blog = blog();

    $data = consoleUserApi('GET', '/init-temp', ['temp_subdomain' => $blog->subdomain])
        ->assertOk()
        ->json();

    expect($data['blogs'])->toHaveCount(1);
    expect($data['blogs'][0]['subdomain'])->not->toBe($blog->subdomain);
});
