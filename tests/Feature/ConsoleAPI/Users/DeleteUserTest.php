<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserDeletedEvent;
use App\Domains\User\Events\UserVariantDeletedEvent;
use App\Models\PostAuthor;
use App\Models\PostTag;
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

    PostAuthor::create([
        'post_id' => 1,
        'user_id' => $user->id
    ]);

    // has
    expect(User::find($user->id))->toBeInstanceOf(User::class);
    expect(UserVariant::where('user_id', $user->id)->count())->toBe(3);
    expect(PostAuthor::where('user_id', $user->id)->count())->toBe(1);

    $this->callConsoleApi('DELETE', "/user/$user->id")
        ->assertOk();

    // nope
    expect(User::find($user->id))->toBeNull();
    expect(UserVariant::where('user_id', $user->id)->count())->toBe(0);
    expect(PostAuthor::where('user_id', $user->id)->count())->toBe(0);

    Event::assertDispatched(UserDeletedEvent::class);
    Event::assertDispatched(UserVariantDeletedEvent::class, 3);

});