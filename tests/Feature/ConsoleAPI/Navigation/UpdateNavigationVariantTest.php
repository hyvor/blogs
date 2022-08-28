<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Domains\Navigation\Events\NavigationVariantChangedEvent;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a variant', function () {
    Event::fake();

    $nav = Navigation::factory()->create(['blog_id' => blog()]);
    $languageId = blog()->languages[0]->id;
    $variant = NavigationVariant::factory()->create([
        'navigation_id' => $nav,
        'language_id' => $languageId,
    ]);

    $name = 'ehw';
    $this->callConsoleApi('PUT', "/navigation/$nav->id/variant", [
        'language_id' => $languageId,
        'name' => $name,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('name', $name)->etc());

    Event::assertDispatched(NavigationVariantChangedEvent::class);
});
