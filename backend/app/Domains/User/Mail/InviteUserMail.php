<?php

namespace App\Domains\User\Mail;

use App\Domains\App\DomainService;
use App\Models\User;
use Hyvor\Internal\Auth\AuthUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class InviteUserMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $link;

    public function __construct(
        public User $user,
        public AuthUser $hyvorUser
    ) {
        $route = URL::temporarySignedRoute('user-accept-invite', now()->addHours(24), [
            'user_id' => $user->id,
        ], absolute: false);
        $this->link = DomainService::getAppUrl() . $route;
    }

    public function build() : static
    {
        return $this->view('emails.invite-user');
    }
}
