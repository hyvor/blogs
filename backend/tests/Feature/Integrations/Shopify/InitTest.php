<?php

namespace Tests\Feature\Integrations\Shopify;

use Illuminate\Support\Facades\URL;

it('requires a valid domain', function () {
    integrationApi('GET', '/shopify', [
        'shop' => 'invalid-shop'
    ])
        ->assertUnprocessable();
});

it('redirects to the console if the user is not found', function () {
    $blog = getShopifyEnabledBlog();

    integrationApi('GET', '/shopify', [
        'shop' => 'test.myshopify.com'
    ])->assertRedirect('/console/' . $blog->subdomain);
});

it('redirects to the oauth endpoint and sets nonce', function () {
    $response = integrationApi('GET', '/shopify', [
        'shop' => 'myshop.myshopify.com'
    ])->assertRedirect();

    $redirect = $response->headers->get('Location');
    $search = parse_url($redirect, PHP_URL_QUERY);
    parse_str($search, $search);

    $nonce = $search['state'];

    expect(session('shopify_nonce'))->toBe($nonce);

    expect($search['client_id'])->toBeString();
    expect($search['redirect_uri'])->toBe(URL::route('shopify-installed'));
    expect($search['scope'])->toBeString();
    expect($search['state'])->toBeString();
    expect($search['grant_options'])->toBeArray();
});
