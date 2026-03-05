<?php

namespace Tests;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;
use Plannr\Laravel\FastRefreshDatabase\Traits\FastRefreshDatabase;

/**
 * @deprecated
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use FastRefreshDatabase;

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

    protected function refreshTestDatabase()
    {
        RefreshDatabaseState::$migrated = true;
        $this->beginDatabaseTransaction();
    }
}
