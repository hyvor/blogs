<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Testing\Fluent\AssertableJson;

it('searches users', function () {
    $name = 'Thisisname';
    $languageId = blog()->languages[0]->id;

    User::factory()
        ->has(
            UserVariant::factory()->state([
                'language_id' => $languageId,
                'name' => $name,
            ]),
            'variants'
        )->create(['blog_id' => blog()]);

    $this->callConsoleApi('GET', '/users/search', [
        'search' => 'Thisis',
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->count(1)
                ->where("0.variants.0.name", $name)
        );
});
