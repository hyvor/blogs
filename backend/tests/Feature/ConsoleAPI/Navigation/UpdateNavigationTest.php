<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Domains\Navigation\Events\NavigationChangedEvent;
use App\Models\Navigation;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a navigation', function () {
    Event::fake();

    $blog = blogWithAccess();
    $navigation = Navigation::factory()->create(['blog_id' => $blog]);

    $url = 'https://example.com/or';
    $type = 'footer';
    consoleApi($blog, 'PATCH', "/navigation/$navigation->id", [
        'url' => $url,
        'type' => $type,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
            ->where('url', $url)
            ->where('type', $type)
            ->etc()
        );

    Event::assertDispatched(NavigationChangedEvent::class);
});
