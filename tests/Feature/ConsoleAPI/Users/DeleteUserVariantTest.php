<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserVariantDeletedEvent;
use Illuminate\Support\Facades\Event;

it('deletes a user variant', function () {

    Event::fake();

    $user = $this->blog->users()->first();

    $variants = $user->variants()->count();

    $this
        ->callConsoleApi('DELETE', "/user/$user->id/variant", [
            'language_id' => $this->blog->languages[1]->id,
        ])
        ->assertOk();

    expect($user->variants()->count())->toBe($variants - 1);

    Event::assertDispatched(UserVariantDeletedEvent::class);

});



it('does not delete primary language variant', function () {
    $user = $this->blog->users()->first();

    $this
        ->callConsoleApi('DELETE', "/user/$user->id/variant", [
            'language_id' => $this->blog->languages[0]->id,
        ])
        ->assertUnprocessable();
});