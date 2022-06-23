<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Data\Enums\UserStatusEnum;
use App\Domains\User\Mail\InviteUserMail;
use App\Models\User;
use Hyvor\HyvorConnecter\Userbase;
use Illuminate\Support\Facades\Mail;

it('resends an invite', function () {
    Mail::fake();

    $user = User::factory()->create([
        'blog_id' => blog(),
        'status' => UserStatusEnum::INVITED,
    ]);

    $email = 'hyvor@hyvor.com';
    Userbase::fake([
        [
            'id' => $user->hyvor_user_id,
            'email' => $email,
        ],
    ]);

    $this->callConsoleApi('POST', "/user/$user->id/resend-invite")
        ->assertOk();

    Mail::assertSent(InviteUserMail::class, function ($mail) use ($email) {
        return $mail->hasTo($email);
    });
});

it('does not send invite to active users', function () {
    Mail::fake();

    $user = User::factory()->create([
        'blog_id' => blog(),
        'status' => UserStatusEnum::ACTIVE,
    ]);

    $this->callConsoleApi('POST', "/user/$user->id/resend-invite")
        ->assertUnprocessable();


    Mail::assertNothingSent();
});
