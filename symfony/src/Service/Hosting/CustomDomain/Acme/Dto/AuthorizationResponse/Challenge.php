<?php

namespace App\Service\Hosting\CustomDomain\Acme\Dto\AuthorizationResponse;

use App\Service\Hosting\CustomDomain\Acme\Dto\AcmeErrorDto;

readonly class Challenge
{
    public function __construct(
        /**
         * @var string 'http-01'|'dns-01'|'tls-alpn-01'
         */
        public string $type,
        public ?string $token,
        public string $url,
        /**
         * @var string|null 'pending'|'processing'|'valid'|'invalid'
         */
        public ?string $status = null,
        public ?AcmeErrorDto $error = null,
    ) {}
}
