<?php

namespace App\Service\User\Event;

use App\Entity\UserVariant;

readonly class UserVariantDeletedEvent
{
    public function __construct(public UserVariant $variant) {}
}
