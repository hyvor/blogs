<?php


use App\Data\Enums\BlogBillingTypeEnum;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Blog\Fillers\UserFiller;
use App\Models\Blog;
use App\Models\ShopifyShop;

function getShopifyEnabledBlog(): Blog
{
    $blog = blog();
    (new LanguageFiller($blog))->fill();
    (new UserFiller($blog))->fill();
    $blog->billing_type = BlogBillingTypeEnum::SHOPIFY;
    $blog->save();

    ShopifyShop::create([
        'blog_id' => $blog->id,
        'domain' => 'test.myshopify.com',
        'access_token' => 'test_token'
    ]);

    return $blog;
}
