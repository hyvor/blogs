<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets pages', function () {
    $this
        ->callConsoleApi('GET', '/pages')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->each(function (AssertableJson $json) {
                $json->where('is_page', true)
                    ->etc();
            });
        });
});
