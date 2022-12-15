<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Data\Objects\DataAPI\BlogObject;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Language\LanguageRepository;

test('language_variant_url in normal pages', function () {
    $blog = blogWithLanguage();
    addLanguage($blog);
    $blog->refresh();

    $blogObject = new BlogObject($blog, $blog->languages[0]);

    testTwigRendering(
        "{{ '{$blog->languages[0]->code}' | language_variant_url }}",
        [
            '_blog' => $blogObject,
            '_route' => [
                'name' => 'index'
            ]
        ],
        "$blogObject->base_url"
    );

    testTwigRendering(
        "{{ '{$blog->languages[1]->code}' | language_variant_url }}",
        [
            '_blog' => $blogObject,
            '_route' => [
                'name' => 'index'
            ]
        ],
        "$blogObject->base_url/{$blog->languages[1]->code}"
    );
});

test('language_variant_url in posts/tags/authors', function () {
    $blog = blogWithLanguage();
    addLanguage($blog);

    $blog->refresh();
    $blog->languages[0]->update(['code' => 'en']);
    $blog->languages[1]->update(['code' => 'fr']);

    $blogObject = new BlogObject($blog, $blog->languages[0]);

    $routes = ['post', 'tag', 'author'];

    foreach ($routes as $route) {

        // current post URL
        testTwigRendering(
            '{{ "en" | language_variant_url }}',
            [
                '_blog' => $blogObject,
                '_route' => [
                    'name' => $route
                ],
                "_$route" => [
                    'url' => 'post-url',
                    'language' => [
                        'code' => 'en'
                    ]
                ]
            ],
            "post-url"
        );

        // URL of a variant
        testTwigRendering(
            '{{ "fr" | language_variant_url }}',
            [
                '_blog' => $blogObject,
                '_route' => [
                    'name' => $route
                ],
                "_$route" => [
                    'url' => 'post-url',
                    'language' => [
                        'code' => 'en'
                    ],
                    'variants' => [
                        [
                            'language' => [
                                'code' => 'fr',
                            ],
                            'url' => 'post-fr-url'
                        ]
                    ]
                ]
            ],
            "post-fr-url"
        );

        // returns blog-level URL when the variant is not found
        testTwigRendering(
            '{{ "fr" | language_variant_url }}',
            [
                '_blog' => $blogObject,
                '_route' => [
                    'name' => $route
                ],
                "_$route" => [
                    'url' => 'post-url',
                    'language' => [
                        'code' => 'en'
                    ],
                    'variants' => []
                ]
            ],
            "$blogObject->base_url/fr"
        );
    }
});
