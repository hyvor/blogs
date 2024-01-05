<?php

namespace Tests\Feature\ConsoleAPI\ApiKeys;

use App\Models\ApiKey;
use Illuminate\Support\Str;

it('regenerates api key', function() {

    $blog = blogWithAccess();
    $key = Str::random(16);
    $apiKey = ApiKey::factory()->create([
        'blog_id' => $blog,
        'api_key' => $key
    ]);

    consoleApi($blog, 'POST', "/api-key/$apiKey->id/regenerate")
        ->assertOk();

    $apiKey->refresh();

    expect($apiKey->api_key)->not->toEqual($key);

});