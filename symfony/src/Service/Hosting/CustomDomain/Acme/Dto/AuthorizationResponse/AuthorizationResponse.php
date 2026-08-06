<?php

namespace App\Service\Hosting\CustomDomain\Acme\Dto\AuthorizationResponse;

use App\Service\Hosting\CustomDomain\Acme\AcmeException;

class AuthorizationResponse
{
    public string $status;
    /**
     * @var Challenge[]
     */
    public array $challenges;

    /**
     * @throws AcmeException
     */
    public function getFirstHttp01Challenge(): Challenge
    {
        foreach ($this->challenges as $challenge) {
            if ($challenge->type === 'http-01') {
                return $challenge;
            }
        }
        throw new AcmeException('No http-01 challenge found in authorization response'); // @codeCoverageIgnore
    }
}
