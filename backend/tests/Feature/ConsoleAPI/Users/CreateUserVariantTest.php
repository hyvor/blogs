<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserVariantCreatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a user variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);
    $language2 = addLanguage($blog);
    $user = addUser($blog);
    addDefaultRoutes($blog, 'author');

    $user->variants()->delete();

    $this->assertEquals(0, $user->variants()->count());

    consoleApi($blog, 'POST', "/user/$user->id/variant", [
            'language_id' => $language->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('bio')->etc());

    consoleApi($blog, 'POST', "/user/$user->id/variant", [
            'language_id' => $language2->id,
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('bio')->etc());

    $this->assertEquals(2, $user->variants()->count());

    Event::assertDispatched(UserVariantCreatedEvent::class);
});
