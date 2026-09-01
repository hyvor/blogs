<?php

namespace App\Api\Console\Object;

use App\Entity\CustomDomainIntent;
use App\Entity\Enum\CustomDomainTlsProvider;

class CustomDomainIntentObject
{
    public int $created_at;
    public string $domain;
    public CustomDomainTlsProvider $tls_provider;
    public bool $has_certificate;

    public function __construct(CustomDomainIntent $intent)
    {
        $this->created_at = $intent->getCreatedAt()->getTimestamp();
        $this->domain = $intent->getDomain();
        $this->tls_provider = $intent->getTlsProvider();
        $this->has_certificate = $intent->getCertificate() !== null;
    }
}
