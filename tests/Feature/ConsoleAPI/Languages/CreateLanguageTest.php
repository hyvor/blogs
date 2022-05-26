<?php

namespace Tests\Feature\ConsoleAPI\Languages;

use Illuminate\Testing\Fluent\AssertableJson;

it('creates a language', function() {

    $code = 'si';
    $name = 'සිංහල';

    $this->callConsoleApi('POST', '/language', [
        'code' => $code,
        'name' => $name
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->has('id')
                ->where('code', $code)
                ->where('name', $name)
                ->etc()
        );

});

it('does not create language if the code already exists', function() {

    $this->callConsoleApi('POST', '/language', [
        'code' => 'en',
        'name' => 'English'
    ])
        ->assertUnprocessable()
        ->assertSee(['Language', 'exists']);

});