<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk;

use App\Models\HyvorTalkWebsite;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

it('does not allow creating if already exists', function () {
    $blog = blogWithAccess();

    HyvorTalkWebsite::create([
        'blog_id' => $blog->id,
        'website_id' => 23
    ]);

    consoleApi($blog, 'post', '/integrations/hyvor-talk')
        ->assertUnprocessable()
        ->assertSee('Hyvor Talk integration already exists');
});

it('creates an integration', function () {
    $response = new JsonMockResponse([
        'id' => 23
    ]);
    $mockHttpClient = new MockHttpClient($response);
    $this->app->bind(HttpClientInterface::class, fn() => $mockHttpClient);

    $blog = blogWithAccess();

    consoleApi($blog, 'post', '/integrations/hyvor-talk')
        ->assertOk()
        ->assertJsonPath('website_id', 23);

    expect($response->getRequestUrl())->toBe(
        'http://talk.hyvor.internal/api/internal/blogs/integration/create-website'
    );
});
