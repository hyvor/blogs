<?php

namespace App\Service\User\Event;

use App\Entity\User;

readonly class UserDeletedEvent
{
    public function __construct(public User $user) {}
}
