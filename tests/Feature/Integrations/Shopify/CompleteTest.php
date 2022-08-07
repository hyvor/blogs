<?php

namespace Tests\Feature\Integrations\Shopify;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Data\Enums\BlogHostingAtEnum;
use App\Domains\Blog\BlogService;
use App\Models\Blog;
use App\Models\ShopifyShop;
use Mockery\MockInterface;

it('returns error when shop is not found', function() {

    $this->callIntegrationEndpoint('GET', '/shopify/complete', [
        'domain' => 'shop.myshopify.com'
    ])->assertUnprocessable()
        ->assertSee('Shop not found');

});

it('returns an error when the shop is already assigned to a blog', function() {

    ShopifyShop::create([
        'blog_id' => 1,
        'domain' => 'shop.myshopify.com',
        'access_token' => 'test'
    ]);

    $this->callIntegrationEndpoint('GET', '/shopify/complete', [
        'domain' => 'shop.myshopify.com'
    ])
        ->assertUnprocessable()
        ->assertSee('Shop already assigned to a blog');

});

it('redirects to auth when the user is not logged in', function() {

    config(['hyvorconnecter.dummy' => false]);

    ShopifyShop::create([
        'domain' => 'shop.myshopify.com',
        'access_token' => 'test'
    ]);

    $this->callIntegrationEndpoint('GET', '/shopify/complete', [
        'domain' => 'shop.myshopify.com'
    ])
        ->assertRedirectContains('/signup?redirect=')
        ->assertRedirectContains(urlencode('/integrations/shopify/complete?domain='));

});

it('creates blog, sets up self hosting, sets blog_id in shopify shop, and redirects to console', function() {

    // prevent calling additional fillers
    $this->mock(BlogService::class, function (MockInterface $mock) {
        $mock->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('callFillers')
            ->once();
    });

    $domain = 'shop.myshopify.com';
    $shop = ShopifyShop::create([
        'domain' => $domain,
        'access_token' => 'test'
    ]);

    $this->callIntegrationEndpoint('GET', '/shopify/complete', [
        'domain' => $domain
    ])->assertRedirect('/console/shop-myshopify-com');

    $blog = Blog::where('subdomain', 'shop-myshopify-com')->first();
    expect($blog)->toBeInstanceOf(Blog::class);
    expect($blog->billing_type)->toBe(BlogBillingTypeEnum::SHOPIFY);
    expect($blog->hosting_at)->toBe(BlogHostingAtEnum::SELF);
    expect($blog->hosting_url)->toBe("https://$domain/a/blog");

    $shop->refresh();
    expect($shop->blog_id)->toBe($blog->id);

});