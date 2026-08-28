<?php

namespace App\Service\Post\Document;

class StepDto
{

    public function __construct(
        public int $version,
        /**
         * @var array<mixed>
         */
        public array $step,
        public string $client_id,
    ) {}

}
