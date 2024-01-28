<?php

namespace App\Domains\User\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUpdatedEvent
{
    use Dispatchable;
    use SerializesModels;

    public User $user;

    public User $userOld;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->userOld = new User($user->getOriginal());
    }
}
