<?php

namespace Tests\Feature\ConsoleAPI\Webhook;

use App\Models\Webhook;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a webhook', function () {

    $blog = blogWithAccess();
    consoleApi($blog, 'POST', '/webhook', [
        'url' => 'https://example.com',
        'events' => ['cache.single']
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('url', 'https://example.com')
                ->has('events', 1)
                ->where('events.0', 'cache.single')
                ->has('id')
                ->has('secret')
        );
});

it('validates url', function () {

    $blog = blogWithAccess();
    consoleApi($blog, 'POST', '/webhook', [
        'url' => '/relative',
        'events' => ['cache.single']
    ])
        ->assertUnprocessable()
        ->assertSee('The url must be a valid URL.');
});

it('validates events', function () {

    $blog = blogWithAccess();
    consoleApi($blog, 'POST', '/webhook', [
        'url' => 'https://example.com',
        'events' => ['invalid.event', 'cache.single']
    ])
        ->assertUnprocessable()
        ->assertSee('events.0 is invalid');
});

it('enforces the limit', function () {

    $blog = blogWithAccess();
    Webhook::factory()->count(config('limits.max_webhooks_per_blog'))->create([
       'blog_id' => $blog
   ]);

    consoleApi($blog, 'POST', '/webhook', [
       'url' => 'https://example.com',
       'events' => ['cache.single']
   ])
       ->assertUnprocessable()
       ->assertSee('Max webhooks limit exceeded');
});
