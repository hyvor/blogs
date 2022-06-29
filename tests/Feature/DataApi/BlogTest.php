<?php

namespace Tests\Feature\DataApi;

use Illuminate\Testing\Fluent\AssertableJson;

it('fetches blog', function () {
    $this->callDataApi('/blog')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('subdomain')
               ->has('name')
               ->etc();
        });
});

it('does not fetch invalid blogs', function () {
    $this->callDataApi('/blog', [], 'tesing_other')
        ->assertNotFound();
});

it('fetches blog with correct language', function () {
    $variant = $this->blog->variants[1];

    $this
        ->callDataApi('/blog', [
            'language' => $variant->language->code,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($variant) {
            $json->where('name', $variant->name)
                ->etc();
        });
});

it('filters key', function () {
    $this
        ->callDataApi('/blog', [
            'keys' => 'subdomain',
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('subdomain')
                ->missing('name');
        });
});
