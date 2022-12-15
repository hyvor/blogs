<?php

namespace Tests\Feature\ConsoleAPI\Redirects;

use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Models\Redirect;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a redirect', function () {
    Event::fake();

    $blog = blogWithAccess();

    $redirect = Redirect::factory()->create(['blog_id' => $blog]);

    $path = '/example';
    $to = 'https://example.com';
    $type = 'temporary';

    consoleApi($blog, 'PUT', "/redirect/$redirect->id", [
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

    $r1 = Redirect::factory()->create(['blog_id' => $blog, 'path' => $path]);
    $r2 = Redirect::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'PUT', "/redirect/$r2->id", [
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertUnprocessable()
        ->assertSee(['path', 'exists']);
});
