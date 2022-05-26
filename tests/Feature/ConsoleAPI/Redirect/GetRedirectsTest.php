<?php

namespace Tests\Feature\ConsoleAPI\Redirect;

use App\Models\Redirect;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function() {

    Redirect::factory()->count(3)->create([
        'blog_id' => blog()
    ]);

});

it('get redirects', function() {

    $this->callConsoleApi('GET', '/redirects', [
        'limit' => 2,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has(2)
            ->each(function (AssertableJson $json) {
                $json->has('id')
                    ->has('path')
                    ->has('to')
                    ->etc();
            })
        );

});

it('works with offset', function() {

    $this->callConsoleApi('GET', '/redirects', [
        'limit' => 2,
        'offset' => 2
    ])
        ->assertOk()
        ->assertJsonCount(1);

});