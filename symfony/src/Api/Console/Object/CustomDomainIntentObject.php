<?php

namespace App\Api\Console\Object;

use App\Entity\CustomDomainIntent;

class CustomDomainIntentObject
{
    public int $created_at;
    public string $domain;

    public function __construct(CustomDomainIntent $intent)
    {
        $this->created_at = $intent->getCreatedAt()->getTimestamp();
        $this->domain = $intent->getDomain();
    }
}
