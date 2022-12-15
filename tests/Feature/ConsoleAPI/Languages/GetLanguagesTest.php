<?php

namespace Tests\Feature\ConsoleAPI\Languages;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets languages', function () {

    $blog = blogWithAccess();
    addLanguage($blog);
    addLanguage($blog);

    consoleApi($blog, 'GET', '/languages')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJson(
            fn (AssertableJson $json) => $json->each(
                fn (AssertableJson $json) => $json->has('id')
                    ->has('code')
                    ->has('name')
                    ->etc()
            )
        );
});
