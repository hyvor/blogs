<?php

namespace Tests\Feature\ConsoleAPI\Users;

use Illuminate\Testing\Fluent\AssertableJson;

it('creates a guest user', function() {

    $languageId = blog()->languages[0]->id;

    $name = 'Hyvor';
    $this->callConsoleApi('POST', '/user/guest', [
        'name' => $name
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where("variants.$languageId.name", $name)
                ->etc()
        );

});