<?php

namespace Tests\Feature\ConsoleAPI\Routes;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets routes', function () {

    $blog = blogWithAccess();
    addDefaultRoutes($blog);

    consoleApi($blog, 'GET', '/routes')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->each(function (AssertableJson $json) {
                $json->has('id')
                    ->has('name')
                    ->has('match')
                    ->has('template')
                    ->etc();
            });
        });
});
