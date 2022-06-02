<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Models\Navigation;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a navigation', function() {

    $navigation = Navigation::factory()->create(['blog_id' => blog()]);

    $url = 'https://example.com/or';
    $this->callConsoleApi('PUT', "/navigation/$navigation->id", [
        'url' => $url
    ])
        ->assertOk()
        ->assertJson(fn(AssertableJson $json) => $json->where('url', $url)->etc());

});