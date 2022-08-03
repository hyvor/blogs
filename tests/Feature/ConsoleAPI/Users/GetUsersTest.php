<?php

namespace Tests\Feature\ConsoleAPI\Users;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets users', function () {
    $this->callConsoleApi('GET', '/users')
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->each(
                fn (AssertableJson $json) => $json->has('id')
                    ->has('role')
                    ->has('status')
                    ->has('variants')
                    ->etc()
            )
        );
});
