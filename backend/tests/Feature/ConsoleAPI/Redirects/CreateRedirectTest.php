<?php

namespace Tests\Feature\ConsoleAPI\Redirects;

use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Models\Redirect;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a static redirect', function () {
    Event::fake();

    $blog = blogWithAccess();

    $path = '/example';
    $to = 'https://example.com';
    $type = 'permanent';

    consoleApi($blog, 'POST', '/redirect', [
        'dynamic' => false,
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json) => $json->where('path', $path)
                ->where('to', $to)
                ->where('type', $type)
                ->etc()
        );

    Event::assertDispatched(RedirectChangedEvent::class);
});

it('creates a dynamic redirect', function () {
    Event::fake();

    $blog = blogWithAccess();

    $path = '/example/(.*)';
    $to = 'https://example.com/$1';
    $type = 'temporary';

    consoleApi($blog, 'POST', '/redirect', [
        'dynamic' => true,
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json) => $json->where('path', $path)
                ->where('to', $to)
                ->where('type', $type)
                ->etc()
        );

    Event::assertDispatched(RedirectChangedEvent::class);
});

it('does not create a static redirect when path is taken', function () {

    $blog = blogWithAccess();

    $path = '/example';
    $to = 'https://example.com';
    $type = 'permanent';

    Redirect::factory()->create(['blog_id' => $blog, 'dynamic' => false, 'path' => $path]);

    consoleApi($blog, 'POST', '/redirect', [
        'dynamic' => false,
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertUnprocessable()
        ->assertSee(['path', 'exists']);
});

it('does not create a dynamic redirect when path is taken', function () {

    $blog = blogWithAccess();

    $path = '/example/(.*)';
    $to = 'https://example.com/$1';
    $type = 'temporary';

    Redirect::factory()->create(['blog_id' => $blog, 'dynamic' => true, 'path' => $path]);

    consoleApi($blog, 'POST', '/redirect', [
        'dynamic' => true,
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertUnprocessable()
        ->assertSee(['path', 'exists']);
});

it('validates the regular expression for dynamic redirects', function () {

    $blog = blogWithAccess();

    $path = '/example/([0-9+)';
    $to = 'https://example.com/$1';
    $type = 'temporary';

    consoleApi($blog, 'POST', '/redirect', [
        'dynamic' => true,
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertUnprocessable()
        ->assertSee('invalid_path_regex');
});

it('validates the dynamic redirect count when creating a dynamic redirect', function () {

    $blog = blogWithAccess();

    $path = '/example/(.*)';
    $to = 'https://example.com/$1';
    $type = 'temporary';

    Redirect::factory()->count(5)->create(['blog_id' => $blog, 'dynamic' => true]);

    consoleApi($blog, 'POST', '/redirect', [
        'dynamic' => true,
        'path' => $path,
        'to' => $to,
        'type' => $type,
    ])
        ->assertUnprocessable()
        ->assertSee('Maximum number of dynamic redirects reached');
});
