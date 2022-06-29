<?php

namespace Tests\Feature\ConsoleAPI\ApiKeys;

use Illuminate\Testing\Fluent\AssertableJson;

it('creates API Key', function() {

    $this->callConsoleApi('POST', '/api-key', [
        'type' => 'console',
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->where('type', 'console')
                ->has('api_key')
                ->etc();
        });

});