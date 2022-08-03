<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserVariantUpdatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates user variant', function () {
    Event::fake();

    $user = $this->blog->users()->first();
    $variant = $user->variants[0];
    $language = $variant->language;

    $name = 'Hey';
    $bio = 'I am hey';
    $location = 'France';

    $this
        ->callConsoleApi('PATCH', "/user/$user->id/variant", [
            'language_id' => $language->id,
            'name' => $name,
            'bio' => $bio,
            'location' => $location,
        ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                    ->where('name', $name)
                    ->where('bio', $bio)
                    ->where('location', $location)
                    ->etc()
        );

    Event::assertDispatched(UserVariantUpdatedEvent::class);
});
