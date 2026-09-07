<?php

namespace App\Service\User\Event;

use App\Entity\UserVariant;

readonly class UserVariantUpdatedEvent
{
    public function __construct(
        public UserVariant $variant,
        public UserVariant $variantOld,
    ) {}
}
