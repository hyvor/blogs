<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets pages', function () {

    $blog = blogWithAccess();

    addPosts($blog, 1, ['is_page' => false]);
    addPosts($blog, 2, ['is_page' => true]);

    consoleApi($blog, 'GET', '/pages')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJson(function (AssertableJson $json) {
            $json->each(function (AssertableJson $json) {
                $json->where('is_page', true)
                    ->etc();
            });
        });
});
