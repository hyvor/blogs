<?php

namespace Tests\Feature\DataApi;

use App\Models\Subscription;
use Illuminate\Testing\Fluent\AssertableJson;

it('fetches blog', function () {
    $blog = blog();
    addBlogVariants($blog, addPrimaryLanguage($blog));

    dataApi($blog, '/blog')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('subdomain')
                ->has('name')
                ->etc();
        });
});

it('does not fetch invalid blogs', function () {
    dataApi('testing-other', '/blog')->assertNotFound();
});

it('fetches blog with correct language', function () {
    $blog = blog();
    $variant = addBlogVariants($blog, addPrimaryLanguage($blog))[0];

    dataApi($blog, '/blog', [
        'language' => $variant->language->code,
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($variant) {
            $json->where('name', $variant->name)
                ->etc();
        });
});

it('filters key', function () {
    $blog = blog();
    addBlogVariants($blog, addPrimaryLanguage($blog));

    dataApi($blog, '/blog', [
        'keys' => 'subdomain',
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('subdomain')
                ->missing('name');
        });
});
