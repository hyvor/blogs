<?php

namespace App\Api\Console\Object;

use App\Entity\CustomDomainSetup;
use App\Entity\Enum\CustomDomainSetupStatus;

class CustomDomainSetupObject
{
    public int $created_at;
    public string $domain;
    public CustomDomainSetupStatus $status;
    public ?string $certificate = null;
    public ?int $valid_from = null;
    public ?int $valid_to = null;

    public function __construct(CustomDomainSetup $customDomainSetup)
    {
        $this->created_at = $customDomainSetup->getCreatedAt()->getTimestamp();
        $this->domain = $customDomainSetup->getDomain();
        $this->status = $customDomainSetup->getStatus();
        $this->certificate = $customDomainSetup->getCertificate();
        $this->valid_from = $customDomainSetup->getValidFrom()?->getTimestamp();
        $this->valid_to = $customDomainSetup->getValidTo()?->getTimestamp();
    }
}