<?php

namespace App\Service\Hosting\CustomDomain\Acme\Dto;

readonly class AcmeErrorDto
{
    public function __construct(
        public ?string $type = null,
        public ?string $detail = null,
        public ?int $status = null,
    ) {}

    public function describe(): string
    {
        $parts = array_filter([
            $this->detail,
            $this->type ? "type: {$this->type}" : null,
            $this->status !== null ? "status: {$this->status}" : null,
        ]);

        return $parts ? implode(', ', $parts) : 'Unknown ACME error';
    }
}
