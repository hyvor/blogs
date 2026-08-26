<?php

namespace App\Service\Post\Document\Exception;

class CheckpointClientBehindException extends \Exception
{

    public function __construct(
        public int $version,
        public array $steps,
        public array $clientIds,
    )
    {
        parent::__construct();
    }


}
