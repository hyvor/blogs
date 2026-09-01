<?php

namespace App\Api\Console\Object;

use App\Entity\Enum\BlogHostingAt;
use App\Entity\Enum\HostingChangeStatus;
use App\Entity\HostingChange;

class HostingChangeObject
{
    public int $id;
    public int $created_at;
    public int $updated_at;
    public BlogHostingAt $from_at;
    public ?string $from_subdomain;
    public ?string $from_domain;
    public ?string $from_url;
    public BlogHostingAt $to_at;
    public ?string $to_subdomain;
    public ?string $to_domain;
    public ?string $to_url;
    public HostingChangeStatus $status;

    public function __construct(HostingChange $hostingChange)
    {
        $this->id = $hostingChange->getId();
        $this->created_at = $hostingChange->getCreatedAt()->getTimestamp();
        $this->updated_at = $hostingChange->getUpdatedAt()->getTimestamp();
        $this->from_at = $hostingChange->getFromAt();
        $this->from_subdomain = $hostingChange->getFromSubdomain();
        $this->from_domain = $hostingChange->getFromDomain();
        $this->from_url = $hostingChange->getFromHostingUrl();
        $this->to_at = $hostingChange->getToAt();
        $this->to_subdomain = $hostingChange->getToSubdomain();
        $this->to_domain = $hostingChange->getToDomain();
        $this->to_url = $hostingChange->getToHostingUrl();
        $this->status = $hostingChange->getStatus();
    }
}
