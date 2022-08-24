<?php

namespace Tests\Feature\ConsoleAPI\Webhook;

use App\Models\Webhook;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a url', function() {

    $webhook = Webhook::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('PATCH', "/webhook/$webhook->id", [
        'url' => 'https://hyvor.com'
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->where('url', 'https://hyvor.com')
                ->etc();
        });

});

it('updates events', function() {

    $webhook = Webhook::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('PATCH', "/webhook/$webhook->id", [
        'events' => ['cache.templates', 'cache.all']
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->where('events', ['cache.templates', 'cache.all'])
                ->etc();
        });

});

it('validates events', function() {

    $webhook = Webhook::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('PATCH', "/webhook/$webhook->id", [
        'events' => ['cache.templates', 'cache.all', 'cache.wrong']
    ])
        ->assertUnprocessable()
        ->assertSee('The selected events.2 is invalid');

});