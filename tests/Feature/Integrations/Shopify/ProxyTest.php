<?php

namespace Tests\Feature\Integrations\Shopify;

use App\Domains\Theme\ThemeFilesRepository;
use App\Models\ShopifyShop;

function generateShopifyProxySignature(array $data): array
{
    $str = collect($data)
        ->sortKeys()
        ->map(fn ($val, $key) => "$key=$val")
        ->implode('');
    $signature = hash_hmac('sha256', $str, config('services.shopify.api_secret_key'));

    return $data + [
        'signature' => $signature
    ];
}

it('requires a valid signature', function () {
    integrationApi('GET', 'shopify/proxy', [
        'shop' => 'shop.myshopify.com',
        'signature' => 'wrong'
    ])->assertUnprocessable()
        ->assertSee('Invalid signature');
});

it('requires a valid shop', function () {
    $data = generateShopifyProxySignature([
        'shop' => 'shop.myshopify.com'
    ]);

    integrationApi('GET', 'shopify/proxy', $data)
        ->assertUnprocessable()
        ->assertSee('Shop not found');
});

it('requires a shop with an assigned blog', function () {
    $data = generateShopifyProxySignature([
        'shop' => 'shop.myshopify.com'
    ]);

    ShopifyShop::create([
        'domain' => 'shop.myshopify.com',
        'access_token' => 'test'
    ]);

    integrationApi('GET', '/shopify/proxy', $data)
        ->assertUnprocessable()
        ->assertSee('No blog is assigned to this shop');
});

it('returns a response', function () {
    $blog = blogWithLanguageAndRoutes();
    $data = generateShopifyProxySignature([
        'shop' => 'shop.myshopify.com'
    ]);

    ShopifyShop::create([
        'domain' => 'shop.myshopify.com',
        'access_token' => 'test',
        'blog_id' => $blog->id
    ]);

    addThemeTemplateFile($blog, 'Hi there!');

    integrationApi('GET', '/shopify/proxy', $data)
        ->assertOk()
        ->assertSee('Hi there!');
});
