<?php

namespace Tests\Case;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;

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
}
