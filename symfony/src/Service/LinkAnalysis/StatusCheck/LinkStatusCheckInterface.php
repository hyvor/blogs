<?php

namespace App\Service\LinkAnalysis\StatusCheck;

use App\Service\LinkAnalysis\Dto\StatusResult;

interface LinkStatusCheckInterface
{
    /**
     * @param string[] $urls
     * @return array<string, StatusResult> URLs as keys and HTTP status codes as values
     */
    public function check(array $urls): array;
}