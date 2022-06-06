<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Support\Facades\Event;

it('deletes the user and its variants', function() {

    Event::fake();

    $user = User::factory()
        ->has(UserVariant::factory()->count(3), 'variants')
        ->create([
            'blog_id' => blog(),
            'role' => 'admin'
        ]);

    // has
    expect(User::find($user->id))->toBeInstanceOf(User::class);
    expect(UserVariant::where('user_id', $user->id)->count())->toBe(3);

    $this->callConsoleApi('DELETE', "/user/$user->id")
        ->assertOk();

    // nope
    expect(User::find($user->id))->toBeNull();
    expect(UserVariant::where('user_id', $user->id)->count())->toBe(0);

    Event::assertDispatched(UserDeletedEvent::class);
    Event::assertDispatched(UserVariantDeletedEvent::class, 3);

});