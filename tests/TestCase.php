<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Indicates whether the default seeder should run before each test.
     * Only for tests with RefreshDatabase
     *
     * @var bool
     */
    protected $seed = true;


    protected function callConsoleApi(string $method, string $endpoint, $data = null) {
        return $this->call($method, URL::to('/api/console/v0/blog/test' . $endpoint), $data);
    }

    protected function callConsoleUserApi(string $method, string $endpoint, $data = null) {
        return $this->call($method, URL::to('/api/console/v0' . $endpoint), $data);
    }

}
