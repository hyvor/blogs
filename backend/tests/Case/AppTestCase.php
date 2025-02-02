<?php

namespace Tests\Case;

use Hyvor\Internal\Auth\AuthFake;
use Tests\CreatesApplication;

class AppTestCase extends \Illuminate\Foundation\Testing\TestCase
{

    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        AuthFake::enable(['id' => 1]);
    }
}
