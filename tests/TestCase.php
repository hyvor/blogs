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


    protected function callDataApi(string $endpoint, $data = [], $subdomain = 'test') {
        $endpoint = trim($endpoint, '/');
        return $this->call('GET', URL::to("/api/data/v0/$subdomain/$endpoint"), $data);
    }

    protected function callConsoleApi(string $method, string $endpoint, $data = null) {
        return $this->call($method, URL::to('/api/console/v0/blog/test' . $endpoint), $data);
    }

    protected function callConsoleUserApi(string $method, string $endpoint, $data = null) {
        return $this->call($method, URL::to('/api/console/v0' . $endpoint), $data);
    }

    protected function callCliAPI(string $method, string $endpoint, $data = []) {
        return $this->call($method, URL::to('/api/cli' . $endpoint), $data);
    }

}
