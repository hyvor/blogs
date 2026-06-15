<?php

namespace App\Tests\Api\Misc;

use App\Api\Misc\MiscController;
use App\Tests\Case\ApiTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MiscController::class)]
class HealthcheckTest extends ApiTestCase
{
    public function testHealthCheck(): void
    {
        $response = $this->client->request('GET', '/api/health');
        $this->assertResponseStatusCodeSame(200);
    }
}