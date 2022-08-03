<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use Illuminate\Testing\Fluent\AssertableJson;

it('validates', function () {
    $this->callConsoleApi('POST', '/blog/variant', [
        'language_id' => null,
    ])->assertUnprocessable();
});

it('does not create a blog variant if it already exists', function () {
    $language = blog()->languages[0];

    $this->callConsoleApi('POST', '/blog/variant', [
        'language_id' => $language->id,
    ])
        ->assertUnprocessable()
        ->assertSee(['Variant', 'already']);
});

it('returns an error if the language is not found', function () {
    $this->callConsoleApi(
        'POST',
        '/blog/variant',
        [
            'language_id' => 12321,
        ]
    )
        ->assertUnprocessable()
        ->assertSee(['Language', 'not']);
});

it('creates a variant', function () {
    $languageId = blog()->languages->firstWhere('is_primary', false)->id;

    blog()->variants()->where('language_id', $languageId)->delete();

    $this->callConsoleApi(
        'POST',
        '/blog/variant',
        [
            'language_id' => $languageId,
        ]
    )
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('language_id', $languageId)
                ->has('name')
                ->has('description')
                ->etc()
        );
});
