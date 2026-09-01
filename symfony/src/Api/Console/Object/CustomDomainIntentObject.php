<?php

namespace App\Api\Console\Object;

use App\Entity\CustomDomainIntent;
use App\Entity\Enum\CustomDomainTlsProvider;

class CustomDomainIntentObject
{
    public int $created_at;
    public string $domain;
    public CustomDomainTlsProvider $tls_provider;
    public ?string $certificate = null;
    public ?int $valid_from = null;
    public ?int $valid_to = null;

    public function __construct(CustomDomainIntent $intent)
    {
        $this->created_at = $intent->getCreatedAt()->getTimestamp();
        $this->domain = $intent->getDomain();
        $this->tls_provider = $intent->getTlsProvider();
        $this->certificate = $intent->getCertificate();
        $this->valid_from = $intent->getValidFrom()?->getTimestamp();
        $this->valid_to = $intent->getValidTo()?->getTimestamp();
    }
}
