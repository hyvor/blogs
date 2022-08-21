<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a link for subscription', function () {
    Http::fake([
        'https://test.myshopify.com/admin/api/2022-07/graphql.json' => Http::response([
            'data' => [
                'appSubscriptionCreate' => [
                    'confirmationUrl' => 'https://confirm.com'
                ]
            ]
        ])
    ]);

    $blog = getShopifyEnabledBlog();
    $this->callConsoleApi('POST', '/billing/shopify/subscription', [], $blog->subdomain)
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('link', 'https://confirm.com'));
});
