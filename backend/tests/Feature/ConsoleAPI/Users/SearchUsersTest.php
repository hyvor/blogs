<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Testing\Fluent\AssertableJson;

it('searches users', function () {
    $name = 'Thisisname';

    $blog = blogWithAccess();
    $languageId = addPrimaryLanguage($blog)->id;
    addDefaultRoutes($blog, 'author');

    User::factory()
        ->has(
            UserVariant::factory()->state([
                'language_id' => $languageId,
                'name' => $name,
            ]),
            'variants'
        )->create(['blog_id' => $blog]);

    consoleApi($blog, 'GET', '/users/search', [
        'search' => 'Thisis',
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->count(1)
                ->where('0.variants.0.name', $name)
        );
});
