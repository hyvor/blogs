<?php

namespace Tests\Feature\Integrations\Shopify;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\BlogIntegrationEnum;
use App\Domains\Blog\Fillers\PostFiller;
use App\Domains\Blog\Fillers\RouteFiller;
use App\Domains\Blog\Fillers\TagFiller;
use App\Domains\Blog\Fillers\ThemeFiller;
use App\Domains\Blog\Fillers\UserFiller;
use App\Models\Blog;
use App\Models\ShopifyShop;
use Illuminate\Support\Facades\Http;
use Mockery;

beforeEach(function () {
    $this->mockFiller = function (string $cls) {
        $themeFillerMock = Mockery::mock($cls)->makePartial();
        $themeFillerMock->shouldReceive('fill')->once();
        $this->app->bind($cls, fn () => $themeFillerMock);
    };
});

it('returns error when shop is not found', function () {
    integrationApi('GET', '/shopify/complete', [
        'domain' => 'shop.myshopify.com'
    ])->assertUnprocessable()
        ->assertSee('Shop not found');
});

it('returns an error when the shop is already assigned to a blog', function () {
    ShopifyShop::create([
        'blog_id' => 1,
        'domain' => 'shop.myshopify.com',
        'access_token' => 'test'
    ]);

    integrationApi('GET', '/shopify/complete', [
        'domain' => 'shop.myshopify.com'
    ])
        ->assertUnprocessable()
        ->assertSee('Shop already assigned to a blog');
});

it('redirects to auth when the user is not logged in', function () {
    config(['hyvorconnecter.dummy' => false]);

    ShopifyShop::create([
        'domain' => 'shop.myshopify.com',
        'access_token' => 'test'
    ]);

    integrationApi('GET', '/shopify/complete', [
        'domain' => 'shop.myshopify.com'
    ])
        ->assertRedirectContains('/signup?redirect=')
        ->assertRedirectContains(urlencode('/integrations/shopify/complete?domain='));
});

it('creates blog, sets up self hosting, sets blog_id in shopify shop, and redirects to console', function () {

    // prevent calling unwanted fillers
    // we only want the navigation filler
    ($this->mockFiller)(UserFiller::class);
    ($this->mockFiller)(TagFiller::class);
    ($this->mockFiller)(PostFiller::class);
    ($this->mockFiller)(RouteFiller::class);
    ($this->mockFiller)(ThemeFiller::class);

    Http::fake([
        'https://shop.myshopify.com/admin/api/2022-07/graphql.json' => Http::response([
            'data' => [
                'shop' => [
                    'name' => 'My Shop',
                    'primaryDomain' => [
                        'url' => 'https://shop.myshopify.com'
                    ]
                ]
            ]
        ])
    ]);

    $domain = 'shop.myshopify.com';
    $shop = ShopifyShop::create([
        'domain' => $domain,
        'access_token' => 'test'
    ]);

    integrationApi('GET', '/shopify/complete', [
        'domain' => $domain
    ])->assertRedirect('/console/my-shop');

    $blog = Blog::where('subdomain', 'my-shop')->first();
    expect($blog)->toBeInstanceOf(Blog::class);
    expect($blog->billing_type)->toBe(BlogBillingTypeEnum::SHOPIFY);
    expect($blog->integration)->toBe(BlogIntegrationEnum::SHOPIFY);
    expect($blog->hosting_at)->toBe(BlogHostingAtEnum::SELF);
    expect($blog->hosting_url)->toBe('https://shop.myshopify.com/a/blog');

    $variant = $blog->variants()->first();
    expect($variant->name)->toBe('My Shop');

    // expect($blog->getMeta('embeddable'))->toBe(true);
    // expect($blog->getMeta('embedding_domains'))->toBe('*');

    $shop->refresh();
    expect($shop->blog_id)->toBe($blog->id);
});
