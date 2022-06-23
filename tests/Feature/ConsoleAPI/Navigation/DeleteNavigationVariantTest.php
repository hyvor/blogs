<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Models\Navigation;
use App\Models\NavigationVariant;

it('deletes navigation variant', function () {
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
});
