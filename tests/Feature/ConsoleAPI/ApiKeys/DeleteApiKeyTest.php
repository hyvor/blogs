<?php

namespace Tests\Feature\ConsoleAPI\ApiKeys;

use App\Models\ApiKey;

it('deletes API key', function() {

    $apiKey = ApiKey::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('DELETE', "/api-key/$apiKey->id")
        ->assertOk();

    expect(ApiKey::find($apiKey->id))->toBeNull();

});