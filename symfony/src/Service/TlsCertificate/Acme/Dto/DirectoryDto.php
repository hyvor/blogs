<?php

namespace App\Service\TlsCertificate\Acme\Dto;

readonly class DirectoryDto
{
    public function __construct(
        public string $newNonce,
        public string $newAccount,
        public string $newOrder,
        public string $revokeCert,
        public string $keyChange,
    ) {}
}