<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Data\Enums\ResultEnum;
use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Domains\Post\Content\PostContentService;
use App\Models\UrlData;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->url = 'https://example.com';
    $this->html = '<div>Hello World</div>';

    // add URL data first
//    UrlData::create([
//        'fetch_type' => UrlDataFetchTypeEnum::EMBED,
//        'url' => $this->url,
//        'final_url' => $this->url,
//        'result' => ResultEnum::OK,
//        'html' => $this->html,
//    ]);

    Http::fake([
        'https://hyvor.cluster/api/internal/unfold/unfold*' => Http::response([
            'embed' => $this->html,
        ])
    ]);
});

test('json to HTML', function () {
    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'embed',
                'attrs' => [
                    'url' => $this->url,
                ],
            ],
        ],
    ];

    $post = PostContentService::getHtml($json, blog());

    expect($post)->toBe("<x-embed data-url=\"$this->url\">$this->html</x-embed>");
});

it('handles when URL is null', function () {
    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'embed',
                'attrs' => [
                    'url' => null
                ],
            ],
        ],
    ];

    $post = PostContentService::getHtml($json, blog());

    expect($post)->toBe('');
});

test('HTML to JSON', function () {
    $html = "<x-embed data-url=\"$this->url\"></x-embed>";

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'figure',
                'content' => [
                    [
                        'type' => 'embed',
                        'attrs' => [
                            'url' => $this->url,
                        ],
                    ],
                ]
            ]
        ],
    ]));
});
