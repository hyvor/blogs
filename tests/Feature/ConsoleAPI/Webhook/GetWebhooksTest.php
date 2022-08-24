<?php

namespace Tests\Feature\ConsoleAPI\Webhook;

use App\Models\Webhook;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets webhooks', function() {

    Webhook::factory()->count(2)->create([
        'blog_id' => blog()
    ]);

    $this->callConsoleApi('GET', '/webhooks')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count(2)
                ->each(function (AssertableJson $json) {
                    $json->has('id')
                        ->has('url')
                        ->has('events')
                        ->has('secret');
                });
        });

});