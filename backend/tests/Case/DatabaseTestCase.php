<?php

namespace Tests\Case;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

class DatabaseTestCase extends AppTestCase
{
    use RefreshDatabase;

    /**
     * Calls the Console API.
     *
     * @param array<string, mixed> $data
     * @return \Illuminate\Testing\TestResponse<JsonResponse>
     */
    public function consoleApi(
        Blog|string $blog,
        string $method,
        string $endpoint,
        array $data = []
    ): \Illuminate\Testing\TestResponse {
        $endpoint = trim($endpoint, '/');
        $subdomain = $blog instanceof Blog ? $blog->subdomain : $blog;
        return $this->call($method, URL::to("/api/console/v0/blog/$subdomain/$endpoint"), $data);
    }

    /**
     * Calls the Console user API (no blog context)
     * @param array<string, mixed> $data
     * @return \Illuminate\Testing\TestResponse<JsonResponse>
     */
    public function consoleUserApi(
        string $method,
        string $endpoint,
        array $data = []
    ): \Illuminate\Testing\TestResponse {
        $endpoint = trim($endpoint, '/');
        return $this->call($method, URL::to("/api/console/v0/$endpoint"), $data);
    }


    /**
     * @param array<string, mixed> $data
     * @return TestResponse<JsonResponse>
     */
    public function dataApi(
        Blog|string $subdomain,
        string $endpoint,
        array $data = []
    ): TestResponse {
        if ($subdomain instanceof Blog) {
            $subdomain = $subdomain->subdomain;
        }

        $endpoint = trim($endpoint, '/');

        return $this->call('GET', URL::to("/api/data/v0/$subdomain/$endpoint"), $data);
    }
}
