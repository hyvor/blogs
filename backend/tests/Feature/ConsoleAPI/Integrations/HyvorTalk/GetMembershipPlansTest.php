<?php

namespace Tests\Feature\ConsoleAPI\Integrations\HyvorTalk;

use App\Models\HyvorTalkWebsite;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

it("gets membership plan names", function () {
    $plansResponse = new JsonMockResponse([
        [
            "name" => "Free",
            "monthly_price" => 10,
        ],
        [
            "name" => "Pro",
            "monthly_price" => 20,
        ],
        [
            "name" => "Business",
            "monthly_price" => 30,
        ],
    ]);

    $mockHttpClient = new MockHttpClient([
        new JsonMockResponse([
            "memberships_enabled" => true,
            "memberships_currency" => "USD",
        ]),
        $plansResponse,
    ]);
    $this->app->bind(HttpClientInterface::class, fn() => $mockHttpClient);

    $blog = blogWithAccess();

    HyvorTalkWebsite::create([
        "blog_id" => $blog->id,
        "website_id" => 23,
    ]);

    consoleApi($blog, "get", "/integrations/hyvor-talk/membership-plans")
        ->assertOk()
        ->assertJsonPath("currency", "USD")
        ->assertJsonPath("plans.0.name", "Free")
        ->assertJsonPath("plans.1.name", "Pro")
        ->assertJsonPath("plans.2.name", "Business");

    // Note: With MockHttpClient, request assertions need to be handled differently
    // You may need to implement custom logic to verify the requests if needed

    expect($plansResponse->getRequestUrl())->toBe(
        "http://talk.hyvor.internal/api/internal/blogs/integration/console-api"
    );

    $json = json_decode($plansResponse->getRequestOptions()['body'], true);
    $data = json_decode(decrypt($json['message'], false), true)['data'];

    expect($data['endpoint'])->toBe('/membership-plans');
    expect($data['method'])->toBe('GET');
    expect($data['website_id'])->toBe(23);
});
