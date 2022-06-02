<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a variant', function() {

    $nav = Navigation::factory()->create(['blog_id' => blog()]);
    $languageId = blog()->languages[0]->id;
    $variant = NavigationVariant::factory()->create([
        'navigation_id' => $nav,
        'language_id' => $languageId
    ]);

    $name = 'ehw';
    $this->callConsoleApi('PATCH', "/navigation/$nav->id/variant", [
        'language_id' => $languageId,
        'name' => $name
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('name', $name)->etc());

});