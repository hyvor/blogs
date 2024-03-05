<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Data\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Support\Facades\URL;

it('accepts invite', function () {
    $blog = blog();
    $user = User::factory()->create([
        'blog_id' => $blog,
        'status' => UserStatusEnum::INVITED,
    ]);

    $url = config('app.url') . URL::temporarySignedRoute('user-accept-invite', now()->addHours(24), [
        'user_id' => $user->id,
    ], false);

    $this->call('GET', $url)
        ->assertOk()
        ->assertSee('Invitation Accepted');

    expect($user->refresh()->status)->toBe(UserStatusEnum::ACTIVE);
});

it('does not accept invitation if the signature is wrong', function () {
    $blog = blog();
    $user = User::factory()->create([
        'blog_id' => $blog,
        'status' => UserStatusEnum::INVITED,
    ]);

    $this->call('GET', "/api/user-accept-invite?user_id=$user->id&signature=wrong")
        ->assertUnprocessable()
        ->assertSee('Invalid Link');

    expect($user->refresh()->status)->toBe(UserStatusEnum::INVITED);
});

it('does not accept invitation if the link is expired', function () {
    $blog = blog();
    $user = User::factory()->create([
        'blog_id' => $blog,
        'status' => UserStatusEnum::INVITED,
    ]);

    $url = config('app.url') . URL::temporarySignedRoute('user-accept-invite', now()->subHour(), [
        'user_id' => $user->id,
    ], false);

    $this->call('GET', $url)
        ->assertUnprocessable()
        ->assertSee('Invalid Link');
});
