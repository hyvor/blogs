<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Models\Navigation;
use App\Models\NavigationVariant;

it('deletes navigation and its variants', function () {
    $nav = Navigation::factory()
        ->has(NavigationVariant::factory()->count(2), 'variants')
        ->create(['blog_id' => blog()]);

    $this->callConsoleApi('DELETE', "/navigation/$nav->id")
        ->assertOk();

    expect(Navigation::find($nav->id))->toBeNull();
    expect(NavigationVariant::where('navigation_id', $nav->id)->count())->toBe(0);
});
