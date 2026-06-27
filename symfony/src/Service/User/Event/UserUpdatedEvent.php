<?php

namespace App\Service\User\Event;

use App\Entity\User;

readonly class UserUpdatedEvent
{
    public function __construct(
        public User $user,
        public User $userOld,
    ) {}
}
