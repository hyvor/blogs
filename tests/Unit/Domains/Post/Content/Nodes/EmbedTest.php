<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Data\Enums\ResultEnum;
use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Domains\Post\Content\PostContentRepository;
use App\Models\UrlData;

beforeEach(function () {
    $this->url = 'https://example.com';
    $this->html = '<div>Hello World</div>';

    // add URL data first
    UrlData::create([
        'fetch_type' => UrlDataFetchTypeEnum::EMBED,
        'url' => $this->url,
        'final_url' => $this->url,
        'result' => ResultEnum::OK,
        'html' => $this->html,
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

    $post = PostContentRepository::getHtml($json, blog());

    expect($post)->toBe("<x-embed>$this->html</x-embed>");
});

test('HTML to JSON', function () {
    $html = "<x-embed data-url=\"$this->url\"></x-embed>";

    $json = PostContentRepository::getJsonFromHtml($html, blog());

    expect($json)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'embed',
                'attrs' => [
                    'url' => $this->url,
                ],
            ],
        ],
    ]));
});
