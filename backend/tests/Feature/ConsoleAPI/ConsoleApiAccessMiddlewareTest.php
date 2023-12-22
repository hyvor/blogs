<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI;

use App\Models\ApiKey;

it('does not allow accessing when no API key is set', function() {

    $blog = blog();

    $this->get("/api/console/v0/blog/$blog->subdomain/blog", [
        'X-API-KEY' => 'my-api-key'
    ])
        ->assertUnprocessable()
        ->assertSee('Invalid API key');

});

it('does not allow accessing when invalid API key is set', function() {

    $blog = blog();

    ApiKey::factory()->create([
        'blog_id' => $blog,
        'type' => 'console',
        'api_key' => 'correct-key'
    ]);

    $this->get("/api/console/v0/blog/$blog->subdomain/blog", [
        'X-API-KEY' => 'wrong-key'
    ])
        ->assertUnprocessable()
        ->assertSee('Invalid API key');

});

it('allows accessing with correct API key', function() {

    $blog = blogWithAccessLanguageAndRoutes();

    ApiKey::factory()->create([
        'blog_id' => $blog,
        'type' => 'console',
        'api_key' => 'correct-key'
    ]);

    $this->get("/api/console/v0/blog/$blog->subdomain/blog", [
        'X-API-KEY' => 'correct-key'
    ])->assertOk();

});

it('should have the key in the same blog', function() {

    $blog = blog();

    ApiKey::factory()->create([
        'blog_id' => blog(),
        'type' => 'console',
        'api_key' => 'correct-key'
    ]);

    $this->get("/api/console/v0/blog/$blog->subdomain/blog", [
        'X-API-KEY' => 'correct-key'
    ])
        ->assertUnprocessable()
        ->assertSee('Invalid API key');

});