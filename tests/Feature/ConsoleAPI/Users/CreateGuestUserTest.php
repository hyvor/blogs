<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserCreatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a guest user', function () {
    Event::fake();

    $languageId = blog()->languages[0]->id;

    $name = 'Hyvor';
    $this->callConsoleApi('POST', '/user/guest', [
        'name' => $name,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('variants.0.name', $name)
                ->etc()
        );

    Event::assertDispatched(UserCreatedEvent::class);
});
