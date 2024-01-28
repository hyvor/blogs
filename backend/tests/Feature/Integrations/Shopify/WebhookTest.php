<?php

namespace Tests\Feature\Integrations\Shopify;

use App\Domains\Blog\Jobs\DeleteBlogJob;
use Illuminate\Support\Facades\Queue;

it('returns ok for other endpoints', function() {
    integrationApi('POST', '/shopify/webhook/customer-data-request')->assertOk();
    integrationApi('POST', '/shopify/webhook/customer-data-erasure')->assertOk();
});

it('requires a valid signature', function () {

    $this->call('POST', '/integrations/shopify/webhook/shop-data-erasure', [
            'shop_domain' => 'test.myshopify.com'
        ], [], [], [
            'HTTP_X_SHOPIFY_HMAC_SHA256' => 'invalid'
        ])
        ->assertUnauthorized();

});

it('calls to delete the blog', function() {

    Queue::fake();

    getShopifyEnabledBlog();

    $data = [
        'shop_domain' => 'test.myshopify.com'
    ];
    $signature = base64_encode(
        hash_hmac('sha256', json_encode($data), config('services.shopify.api_secret_key'), true)
    );

    $this->call('POST', '/integrations/shopify/webhook/shop-data-erasure', $data, [], [], [
        'HTTP_X_SHOPIFY_HMAC_SHA256' => $signature
    ], json_encode($data))
        ->assertOk();

    Queue::assertPushed(DeleteBlogJob::class);

});