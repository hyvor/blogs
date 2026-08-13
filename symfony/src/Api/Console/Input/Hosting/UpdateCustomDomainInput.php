<?php

namespace App\Api\Console\Input\Hosting;

use App\Entity\Enum\CustomDomainTlsProvider;

class UpdateCustomDomainInput
{
    public ?string $new_domain = null;

    public ?CustomDomainTlsProvider $tls_provider = null;

    public ?string $tls_private_key = null;

    public ?string $tls_certificate = null;
}
