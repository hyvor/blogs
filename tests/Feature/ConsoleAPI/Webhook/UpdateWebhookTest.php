<?php

namespace Tests\Feature\ConsoleAPI\Webhook;

use App\Models\Webhook;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a url', function () {
    $blog = blogWithAccess();
    $webhook = Webhook::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'PATCH', "/webhook/$webhook->id", [
        'url' => 'https://hyvor.com'
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->where('url', 'https://hyvor.com')
                ->etc();
        });
});

it('updates events', function () {
    $blog = blogWithAccess();
    $webhook = Webhook::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'PATCH', "/webhook/$webhook->id", [
        'events' => ['cache.templates', 'cache.all']
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->where('events', ['cache.templates', 'cache.all'])
                ->etc();
        });
});

it('validates events', function () {

    $blog = blogWithAccess();
    $webhook = Webhook::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'PATCH', "/webhook/$webhook->id", [
        'events' => ['cache.templates', 'cache.all', 'cache.wrong']
    ])
        ->assertUnprocessable()
        ->assertSee('The selected events.2 is invalid');
});
