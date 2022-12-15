<?php

namespace Tests\Feature\ConsoleAPI\Tags;

use App\Models\Tag;
use Illuminate\Testing\Fluent\AssertableJson;

it('fetches tags', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    addTags($blog, 4);

    consoleApi($blog, 'GET', '/tags', [
            'limit' => 2,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count(2)
                ->has('0', function (AssertableJson $json) {
                    $json->has('id')
                        ->has('slug')
                        ->etc();
                });
        });
});

it('fetches tags with offset', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    addTags($blog, 2);

    consoleApi($blog, 'GET', 'tags', [
            'limit' => 1,
            'offset' => 1,
        ])
        ->assertOk()
        ->assertJsonCount(1);

});
