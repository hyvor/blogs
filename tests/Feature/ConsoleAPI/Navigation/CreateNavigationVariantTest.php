<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Models\Navigation;
use Illuminate\Testing\Fluent\AssertableJson;

it('requires a valid language ID', function () {
    $nav = Navigation::factory()->create(['blog_id' => blog()]);

    $this->callConsoleApi('POST', "/navigation/$nav->id/variant", [
        'name' => 'Hi',
        'language_id' => 100,
    ])
        ->assertUnprocessable()
        ->assertSee('Language not found');
});

it('creates a navigation variant', function () {
    $nav = Navigation::factory()->create(['blog_id' => blog()]);

    $name = 'Hyvor';
    $this->callConsoleApi('POST', "/navigation/$nav->id/variant", [
        'name' => $name,
        'language_id' => blog()->languages[0]->id,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('name', $name)->etc());
});
