<?php

namespace App\Api\Console\Object;

use App\Entity\CustomDomain;
use App\Entity\Enum\CustomDomainTlsProvider;

class CustomDomainObject
{
    public int $created_at;
    public string $domain;
    public CustomDomainTlsProvider $tls_provider;
    public ?string $certificate = null;
    public ?int $valid_from = null;
    public ?int $valid_to = null;

    public function __construct(CustomDomain $customDomain)
    {
        $this->created_at = $customDomain->getCreatedAt()->getTimestamp();
        $this->domain = $customDomain->getDomain();
        $this->tls_provider = $customDomain->getTlsProvider();
        $this->certificate = $customDomain->getCertificate();
        $this->valid_from = $customDomain->getValidFrom()?->getTimestamp();
        $this->valid_to = $customDomain->getValidTo()?->getTimestamp();
    }
}
