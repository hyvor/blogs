<?php

namespace Tests\Feature\ConsoleAPI\Redirects;

use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Models\Redirect;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a redirect', function () {
    Event::fake();

    $blog = blogWithAccess();

    $path = '/example';
    $to = 'https://example.com';
    $type = 'permanent';

    consoleApi($blog, 'POST', '/redirect', [
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('path', $path)
                ->where('to', $to)
                ->where('type', $type)
                ->etc()
        );

    Event::assertDispatched(RedirectChangedEvent::class);
});

it('does not create a redirect when path is taken', function () {

    $blog = blogWithAccess();

    $path = '/example';
    $to = 'https://example.com';
    $type = 'permanent';

    Redirect::factory()->create(['blog_id' => $blog, 'path' => $path]);

    consoleApi($blog, 'POST', '/redirect', [
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertUnprocessable()
        ->assertSee(['path', 'exists']);
});

/*
 * Path redirects were removed because we need the full URL when
 * generating redirect responses
 *
 * it('creates a redirect to another path', function() {

    $path = '/example';
    $to = '/example2';
    $type = 'temporary';

    $this->callConsoleApi('POST', '/redirect', [
        'path' => $path,
        'to' => $to,
        'type' => $type
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
        $json->where('path', $path)
            ->where('to', $to)
            ->where('type', $type)
            ->etc()
        );

});*/
