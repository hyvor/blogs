<?php

namespace App\Service\Hosting\CustomDomain\Acme\Dto;

use App\Service\Hosting\CustomDomain\Acme\AcmeException;

readonly class OrderResponse
{
    /**
     * @param string[] $authorizations
     */
    public function __construct(
        public string  $status,
        public string  $finalize,
        public array   $authorizations,
        public ?string $certificate = null,
        public ?AcmeErrorDto $error = null,
    ) {}

    /**
     * @throws AcmeException
     */
    public function firstAuthorizationUrl(): string
    {
        if (count($this->authorizations) === 0) {
            throw new AcmeException('No authorizations found in order response'); // @codeCoverageIgnore
        }

        return $this->authorizations[0];
    }
}
