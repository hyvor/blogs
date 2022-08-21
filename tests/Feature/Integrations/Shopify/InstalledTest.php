<?php

namespace Tests\Feature\Integrations\Shopify;

use App\Models\ShopifyShop;
use Illuminate\Support\Facades\Http;

it('fails when hmac is wrong', function() {

    $data = [
        'code' => 'test',
        'shop' => 'shop.myshopify.com',
        'state' => 'nonce',
        'hmac' => 'wrong'
    ];

    $this->callIntegrationEndpoint('GET', 'shopify/installed', $data)
        ->assertUnprocessable()
        ->assertSee('HMAC hash is invalid');

});

it('fails when nonce is wrong', function() {

    $data = [
        'code' => 'test',
        'shop' => 'shop.myshopify.com',
        'state' => 'nonce'
    ];
    $data += [
        'hmac' => hash_hmac('sha256', http_build_query($data), config('services.shopify.api_secret_key'))
    ];

    $this->callIntegrationEndpoint('GET', 'shopify/installed', $data)
        ->assertUnprocessable()
        ->assertSee('Nonce is invalid');

});

it('gets the access token and creates a shop', function() {

    $domain = 'shop.myshopify.com';
    Http::fake([
        "https://$domain/admin/oauth/access_token" => Http::response([
            'access_token' => 'test-access',
            'scope' => 'something'
        ])
    ]);

    session(['shopify_nonce' => 'nonce']);

    $data = [
        'code' => 'test',
        'shop' => $domain,
        'state' => 'nonce'
    ];
    $data += [
        'hmac' => hash_hmac('sha256', http_build_query($data), config('services.shopify.api_secret_key'))
    ];

    $this->callIntegrationEndpoint('GET', 'shopify/installed', $data)
        ->assertRedirectContains('shopify/complete');

    $shop = ShopifyShop::where('domain', $domain)->first();
    expect($shop)->toBeInstanceOf(ShopifyShop::class);
    expect($shop->access_token)->toBe('test-access');

});