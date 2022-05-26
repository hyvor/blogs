<?php

namespace Tests\Feature\ConsoleAPI\Language;

use Illuminate\Testing\Fluent\AssertableJson;

it('updates the language', function() {

    $language = blog()->languages[0];

    $code = 'si';
    $name = 'සිංහල';

    $this->callConsoleApi('PUT', "/language/$language->id", [
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

    $language->refresh();

    expect($language->code)->toBe($code);
    expect($language->name)->toBe($name);

});

it('cannot take other languages', function() {

    $language = blog()->languages[0];
    $language2 = blog()->languages[1];

    $this->callConsoleApi('PUT', "/language/$language->id", [
        'code' => $language2->code,
        'name' => 'some name'
    ])
        ->assertUnprocessable()
        ->assertSee(['code', 'exists']);

});