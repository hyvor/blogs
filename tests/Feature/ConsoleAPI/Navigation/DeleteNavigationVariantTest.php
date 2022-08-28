<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Domains\Navigation\Events\NavigationVariantChangedEvent;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Support\Facades\Event;

it('deletes navigation variant', function () {
    Event::fake();

    $nav = Navigation::factory()->create(['blog_id' => blog()]);
    $languageId = blog()->languages[0]->id;
    $variant = NavigationVariant::factory()->create([
        'navigation_id' => $nav,
        'language_id' => $languageId,
    ]);

    $this->callConsoleApi('DELETE', "/navigation/$nav->id/variant", [
        'language_id' => $languageId,
    ])
        ->assertOk();

    expect(NavigationVariant::find($variant->id))->toBeNull();

    Event::assertDispatched(NavigationVariantChangedEvent::class);
});
