<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserCreatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a guest user', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog, 'author');

    $name = 'Hyvor';
    consoleApi($blog, 'POST', '/user/guest', [
        'name' => $name,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('variants.0.name', $name)
                ->etc()
        );

    Event::assertDispatched(UserCreatedEvent::class);
});



it('fails when limits are exceeded', function() {
    $blog = blogWithAccess();
    $blog->setCount('users', 2);

    consoleApi($blog, 'POST', '/user/guest', [
        'name' => 'guest'
    ])
        ->assertUnprocessable()
        ->assertSee('Max users limit exceeded. Please upgrade your plan');
});