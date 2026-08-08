<?php

namespace App\Api\Console\Input\Hosting;

class UpdateCustomDomainInput
{
    public ?string $new_domain = null;

    public ?string $tls_private_key = null;

    public ?string $tls_certificate = null;
}
