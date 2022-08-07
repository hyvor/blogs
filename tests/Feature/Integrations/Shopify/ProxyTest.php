<?php

namespace Tests\Feature\Integrations\Shopify;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\ShopifyShop;

function generateShopifyProxySignature(array $data) : array {
    $str = collect($data)
        ->sortKeys()
        ->map(fn ($val, $key) => "$key=$val")
        ->implode('');
    $signature = hash_hmac('sha256', $str, config('integrations.shopify.api_secret_key'));

    return $data + [
        'signature' => $signature
    ];
}

it('requires a valid signature', function() {

    $this->callIntegrationEndpoint('GET', 'shopify/proxy', [
        'shop' => 'shop.myshopify.com',
        'signature' => 'wrong'
    ])->assertUnprocessable()
        ->assertSee('Invalid signature');

});

it('requires a valid shop', function() {

    $data = generateShopifyProxySignature([
        'shop' => 'shop.myshopify.com'
    ]);

    $this->callIntegrationEndpoint('GET', 'shopify/proxy', $data)
        ->assertUnprocessable()
        ->assertSee('Shop not found');

});

it('requires a shop with an assigned blog', function() {

    $data = generateShopifyProxySignature([
        'shop' => 'shop.myshopify.com'
    ]);

    ShopifyShop::create([
        'domain' => 'shop.myshopify.com',
        'access_token' => 'test'
    ]);

    $this->callIntegrationEndpoint('GET', '/shopify/proxy', $data)
        ->assertUnprocessable()
        ->assertSee('No blog is assigned to this shop');

});

it('returns response object', function() {

    $blog = newBlog();
    (new LanguageFiller($blog))->fill();
    (new RouteFiller($blog))->fill();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'index.twig',
        'testing index'
    );

    $data = generateShopifyProxySignature([
        'shop' => 'shop.myshopify.com'
    ]);

    ShopifyShop::create([
        'domain' => 'shop.myshopify.com',
        'access_token' => 'test',
        'blog_id' => $blog->id
    ]);

    $this->callIntegrationEndpoint('GET', '/shopify/proxy', $data)
        ->assertOk()
        ->assertSee('testing index');


});