<?php

namespace Tests\Feature\ConsoleAPI\ApiKeys;

use Illuminate\Testing\Fluent\AssertableJson;

it('creates API Key', function() {

    $this->callConsoleApi('POST', '/api-key', [
        'name' => 'Console API Key',
        'type' => 'console',
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->where('type', 'console')
                ->where('name', 'Console API Key')
                ->has('api_key')
                ->etc();
        });

});