<?php

namespace Tests\Feature\ConsoleAPI\User;

use Illuminate\Testing\Fluent\AssertableJson;

// To run the user tests - php artisan test  --filter 'CreateUserVariantTest'

it('validate the user Id', function() {
    $this->callConsoleApi('POST', '/user/variant', [
        'userId' => null
    ])->assertUnprocessable();
});

it('validate the language Id', function() {
    $this->callConsoleApi('POST', '/user/variant', [
        'languageId' => null
    ])->assertUnprocessable();
});

it('does not create a user variant if it already exists', function() {
    
    $language = blog()->languages[0];

    $this->callConsoleApi('POST', '/user/variant', [
        'languageId' => $language->id
        ])
        ->assertUnprocessable();
});

it('returns an error if the language is not found', function() {

    $this->callConsoleApi('POST', '/user/variant',[
            'userId' => 1,
            'languageId' => 12321
        ])
        ->assertUnprocessable()
        ->assertSee(['Language', 'not']);

});