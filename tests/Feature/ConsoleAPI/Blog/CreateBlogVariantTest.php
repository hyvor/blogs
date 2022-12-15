<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use Illuminate\Testing\Fluent\AssertableJson;

it('validates', function () {
    $blog = blogWithAccess();
    consoleApi($blog, 'POST', '/blog/variant', [
        'language_id' => null,
    ])->assertUnprocessable();
});

it('does not create a blog variant if it already exists', function () {

    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);

    addBlogVariants($blog, $language);

    consoleApi($blog, 'POST', '/blog/variant', [
        'language_id' => $language->id,
    ])
        ->assertUnprocessable()
        ->assertSee(['Variant', 'already']);
});

it('returns an error if the language is not found', function () {

    $blog = blogWithAccess();

    consoleApi(
        $blog,
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

    $blog = blogWithAccess();
    $language = addLanguage($blog);
    addBlogVariants($blog, $language);

    $blog->variants()->where('language_id', $language->id)->delete();

    consoleApi($blog,
        'POST',
        '/blog/variant',
        [
            'language_id' => $language->id,
        ]
    )
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('language_id', $language->id)
                ->has('name')
                ->has('description')
                ->etc()
        );
});
