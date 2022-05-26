<?php
namespace Tests\Feature\ConsoleAPI\Languages;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets languages', function() {

    $this->callConsoleApi('GET', '/languages')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->each(fn (AssertableJson $json) =>
                $json->has('id')
                    ->has('code')
                    ->has('name')
                    ->etc()
            )
        );

});