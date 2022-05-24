<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function() {
    $this->language = blog()->languages[0];
});

it('validates', function() {
    $this->callConsoleApi('PATCH', '/blog/variant')->assertUnprocessable();
    $this->callConsoleApi('PATCH', '/blog/variant',
        ['language_id' => 2, 'name' => false]
    )->assertUnprocessable();
});

it('updates name', function() {

    $name = 'Name';

    $this->callConsoleApi('PATCH', '/blog/variant',
        [
            'language_id' => $this->language->id,
            'name' => $name
        ]
    )
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json
                ->where('name', $name)
                ->etc()
        );

});

it('updates description', function() {

    $description = 'Hello world';

    $this->callConsoleApi('PATCH', '/blog/variant',
        [
            'language_id' => $this->language->id,
            'description' => $description
        ]
    )
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json
                ->where('description', $description)
                ->etc()
        );

});