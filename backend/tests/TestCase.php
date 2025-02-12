<?php

namespace Tests;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

/**
 * @deprecated
 */
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
    protected $seed = false;

    protected function callCliAPI(string|Blog $subdomain, string $method, string $endpoint, $data = [])
    {
        $endpoint = trim($endpoint, '/');

        if ($subdomain instanceof Blog) {
            $subdomain = $subdomain->subdomain;
        }

        return $this->call($method, URL::to("/api/cli/$subdomain/$endpoint"), $data);
    }

}
