<?php

namespace App\Api\Console\Input\Hosting;

use App\Entity\Enum\CustomDomainTlsProvider;

class CreateCustomDomainInput
{
    public string $domain;

    public CustomDomainTlsProvider $tls_provider = CustomDomainTlsProvider::AUTO;

    public ?string $tls_private_key = null;

    public ?string $tls_certificate = null;
}
