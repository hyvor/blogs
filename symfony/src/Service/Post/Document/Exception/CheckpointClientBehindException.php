<?php

namespace App\Service\Post\Document\Exception;

use App\Service\Post\Document\StepDto;

class CheckpointClientBehindException extends \Exception
{

    public function __construct(
        public int $version,
        /**
         * @var StepDto[]
         */
        public array $steps,
    )
    {
        parent::__construct();
    }


}
