<?php

namespace Tests\Feature\ConsoleAPI\ApiMisc;

use App\Models\Theme;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets themes', function() {

    Theme::factory()->count(3)->create();

    $this->callConsoleMiscApi('GET', '/themes')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json
                ->count(3)
                ->each(fn (AssertableJson $json) =>
                    $json->has('id')
                        ->has('type')
                        ->has('name')
                )
        );

});