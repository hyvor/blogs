<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserVariantCreatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a user variant', function () {
    Event::fake();

    $language = $this->blog->languages[0];
    $language2 = $this->blog->languages[1];
    $user = $this->blog->users()->first();

    $user->variants()->delete();

    $this->assertEquals(0, $user->variants()->count());

    $this
        ->callConsoleApi('POST', "/user/$user->id/variant", [
            'language_id' => $language->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('bio')->etc());

    $this
        ->callConsoleApi('POST', "/user/$user->id/variant", [
            'language_id' => $language2->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('bio')->etc());

    $this->assertEquals(2, $user->variants()->count());

    Event::assertDispatched(UserVariantCreatedEvent::class);
});
