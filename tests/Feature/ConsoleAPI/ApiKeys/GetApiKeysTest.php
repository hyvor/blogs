<?php

namespace Tests\Feature\ConsoleAPI\ApiKeys;

use App\Models\ApiKey;
use Illuminate\Testing\Fluent\AssertableJson;

it('gets API keys', function () {
    ApiKey::factory()->count(3)->create(['blog_id' => blog()]);

    $this->callConsoleApi('GET', '/api-keys')
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->count(3)
                ->each(
                    fn (AssertableJson $json) => $json->has('id')
                        ->has('type')
                        ->has('api_key')
                        ->has('name')
                )
        );
});
