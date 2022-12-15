<?php

namespace Tests\Feature\ConsoleAPI\Routes;

use App\Domains\Route\Events\RouteChangedEvent;
use App\Models\Route;
use Illuminate\Support\Facades\Event;

it('deletes a route', function () {
    Event::fake();

    $blog = blogWithAccess();

    $route = Route::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'DELETE', "/route/$route->id");

    expect(Route::find($route->id))->toBeNull();

    Event::assertDispatched(RouteChangedEvent::class);
});
