<?php

namespace Tests\Unit\Domains\User\Mail;

use App\Domains\User\Mail\InviteUserMail;
use App\Models\User;
use Hyvor\Internal\Auth\AuthFake;

it('has content', function () {
    $user = User::factory()->create();
    $hyvorUser = AuthFake::generateUser();
    $blog = $user->blog;

    $mailable = new InviteUserMail($user, $hyvorUser);

    $mailable->assertSeeInHtml($hyvorUser->name);
    $mailable->assertSeeInHtml("Invitation to join $blog->subdomain");
    $mailable->assertSeeInHtml('/api/user-accept-invite');
    $mailable->assertSeeInHtml("user_id=$user->id&amp;signature=", false);
});
