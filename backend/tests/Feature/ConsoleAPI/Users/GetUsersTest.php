<?php

namespace Tests\Feature\ConsoleAPI\Users;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets users', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog, 'author');
    addUsers($blog, 3);

    consoleApi($blog, 'GET', '/users')
        ->assertOk()
        ->assertJsonCount(3)
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
