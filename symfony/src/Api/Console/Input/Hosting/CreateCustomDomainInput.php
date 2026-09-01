<?php

namespace App\Api\Console\Input\Hosting;

use App\Entity\Enum\CustomDomainTlsProvider;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCustomDomainInput
{
    public string $domain;

    public CustomDomainTlsProvider $tls_provider = CustomDomainTlsProvider::AUTO;

    #[Assert\When(
        'this.tls_provider.value === "custom"',
        new Assert\NotBlank(
            message: 'TLS private key is required when TLS provider is manual',
        )
    )]
    public ?string $tls_private_key = null;

    #[Assert\When(
        'this.tls_provider.value === "custom"',
        new Assert\NotBlank(
            message: 'TLS certificate is required when TLS provider is manual',
        )
    )]
    public ?string $tls_certificate = null;
}
