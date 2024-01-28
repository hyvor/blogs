<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Domains\Navigation\Events\NavigationChangedEvent;
use App\Models\Navigation;
use App\Models\NavigationVariant;
use Illuminate\Support\Facades\Event;

it('deletes navigation and its variants', function () {
    Event::fake();

    $blog = blogWithAccess();

    $nav = Navigation::factory()
        ->has(NavigationVariant::factory()->count(2), 'variants')
        ->create(['blog_id' => $blog]);

    consoleApi($blog, 'DELETE', "/navigation/$nav->id")
        ->assertOk();

    expect(Navigation::find($nav->id))->toBeNull();
    expect(NavigationVariant::where('navigation_id', $nav->id)->count())->toBe(0);

    Event::assertDispatched(NavigationChangedEvent::class);
});
