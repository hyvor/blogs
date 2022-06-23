<?php

namespace App\Domains\User\Mail;

use App\Models\User;
use Hyvor\HyvorConnecter\HyvorUser;
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
        public HyvorUser $hyvorUser
    ) {
        $this->link = URL::temporarySignedRoute('user-accept-invite', now()->addHours(24), [
            'user_id' => $user->id,
        ]);
    }

    public function build()
    {
        return $this->view('emails.invite-user');
    }
}
