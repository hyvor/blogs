<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserVariantDeletedEvent;
use Illuminate\Support\Facades\Event;

it('deletes a user variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addLanguage($blog);
    $user = addUser($blog);

    $variants = $user->variants()->count();

    consoleApi($blog, 'DELETE', "/user/$user->id/variant", [
            'language_id' => $blog->languages[1]->id,
        ])
        ->assertOk();

    expect($user->variants()->count())->toBe($variants - 1);

    Event::assertDispatched(UserVariantDeletedEvent::class);
});

it('does not delete primary language variant', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    $user = addUser($blog);

    consoleApi($blog, 'DELETE', "/user/$user->id/variant", [
            'language_id' => $blog->languages[0]->id,
        ])
        ->assertUnprocessable();
});
