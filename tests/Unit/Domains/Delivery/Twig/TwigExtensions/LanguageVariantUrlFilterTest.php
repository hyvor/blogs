<?php

namespace Tests\Unit\Domains\Delivery\Twig\TwigExtensions;

use App\Data\Objects\DataAPI\BlogObject;
use App\Domains\Blog\Fillers\LanguageFiller;
use App\Domains\Language\LanguageRepository;

test('language_variant_url in normal pages', function () {
    $blog = newBlog();
    (new LanguageFiller($blog))->fill();

    LanguageRepository::createLanguage(
        $blog,
        'fr',
        'French'
    );

    $blogObject = new BlogObject($blog, $blog->languages[0]);

    testTwigRendering(
        '{{ "en" | language_variant_url }}',
        [
            '_blog' => $blogObject,
            '_route' => [
                'name' => 'index'
            ]
        ],
        "$blogObject->base_url"
    );

    testTwigRendering(
        '{{ "fr" | language_variant_url }}',
        [
            '_blog' => $blogObject,
            '_route' => [
                'name' => 'index'
            ]
        ],
        "$blogObject->base_url/fr"
    );
});

test('language_variant_url in posts/tags/authors', function () {
    $blog = newBlog();
    (new LanguageFiller($blog))->fill();

    LanguageRepository::createLanguage(
        $blog,
        'fr',
        'French'
    );

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
