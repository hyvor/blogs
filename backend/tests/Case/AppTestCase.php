<?php

namespace Tests\Case;

use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\MockComms;
use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class AppTestCase extends TestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        AuthFake::enable(
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin'
            )
        );
        app()->singleton(CommsInterface::class, MockComms::class);
    }

    public function getComms(): MockComms
    {
        $comms = app(CommsInterface::class);
        $this->assertInstanceOf(MockComms::class, $comms);
        return $comms;
    }
}
