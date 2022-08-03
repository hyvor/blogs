<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     * Only for tests with RefreshDatabase
     *
     * @var bool
     */
    protected $seed = true;

    protected function callDataApi(
        string $endpoint,
        $data = [],
        $subdomain = 'test'
    ): TestResponse {
        $endpoint = trim($endpoint, '/');

        return $this->call('GET', URL::to("/api/data/v0/$subdomain/$endpoint"), $data);
    }

    protected function callConsoleApi(
        string $method,
        string $endpoint,
        $data = [],
        $subdomain = 'test'
    ): TestResponse {
        $endpoint = trim($endpoint, '/');

        return $this->call($method, URL::to("/api/console/v0/blog/$subdomain/$endpoint"), $data);
    }

    protected function callConsoleUserApi(string $method, string $endpoint, $data = []): TestResponse
    {
        return $this->call($method, URL::to('/api/console/v0'.$endpoint), $data);
    }

    protected function callConsoleMiscApi(string $method, string $endpoint, $data = []): TestResponse
    {
        return $this->call($method, URL::to('/api/console/v0/misc'.$endpoint), $data);
    }

    protected function callCliAPI(string $method, string $endpoint, $data = [], $subdomain = 'dev')
    {
        $endpoint = trim($endpoint, '/');

        return $this->call($method, URL::to("/api/cli/$subdomain/$endpoint"), $data);
    }

    protected function callDeliveryApi(string $endpoint, $data = [], $subdomain = 'test'): TestResponse
    {
        $endpoint = trim($endpoint, '/');

        return $this->call('GET', URL::to("/api/delivery/v0/$subdomain/$endpoint"), $data);
    }
}
