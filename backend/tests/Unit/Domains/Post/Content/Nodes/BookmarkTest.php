<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Theme\ThemeFilesRepository;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->url = 'https://example.com';
    $this->title = 'I am title';
    $this->description = 'I am description';
    $this->thumbnail_url = '/img.png';
    $this->icon_url = '/icon.ico';
    $this->site = 'Youtube';

    Http::fake([
        'https://hyvor.cluster/api/internal/unfold/unfold*' => Http::response([
            'lastUrl' => $this->url,
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnailUrl' => $this->thumbnail_url,
            'iconUrl' => $this->icon_url,
            'siteName' => $this->site,
        ])
    ]);

    // add URL data first
//    UrlData::create([
//        'result' => ResultEnum::OK,
//        'fetch_type' => UrlDataFetchTypeEnum::LINK,
//        'url' => $this->url,
//        'final_url' => $this->url,
//        'title' => $this->title,
//        'description' => $this->description,
//        'thumbnail_url' => $this->thumbnail_url,
//        'icon_url' => $this->icon_url,
//        'site' => $this->site,
//    ]);
});

test('JSON to HTML', function () {
    $json = json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'bookmark',
                'attrs' => [
                    'url' => $this->url,
                ],
            ],
        ],
    ]);

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toContain(
        $this->url,
        $this->title,
        $this->description,
    );

    $this->assertStringNotContainsString($this->site, $html);
});

test('html to json', function () {
    $html = <<<HTML
    <a class="bookmark" data-url="https://talk.hyvor.com"></a>
    HTML;

    $json = PostContentService::getJsonFromHtml($html, blog());

    expect($json)->toBe(json_encode([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'bookmark',
                'attrs' => [
                    'url' => 'https://talk.hyvor.com',
                ],
            ],
        ],
    ]));
});

test('custom template', function () {
    $template = <<<'TWIG'
        <a class="bookmark">
            {{ data.url }}
            {{ data.title }}
            {{ data.description }}
            {{ data.site }}
            {{ data.thumbnail_url }}
            {{ data.icon_url }}
        </a>
    TWIG;

    $blog = blog();

    ThemeFilesRepository::createOrUpdateFile(
        $blog,
        ThemeFileFolderEnum::TEMPLATES,
        'node-bookmark.twig',
        $template
    );

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'bookmark',
                'attrs' => [
                    'url' => $this->url,
                ],
            ],
        ],
    ];

    $html = PostContentService::getHtml($json, $blog);

    expect($html)->toContain(
        $this->url,
        $this->title,
        $this->description,
        $this->site,
        $this->thumbnail_url,
        $this->icon_url
    );
});
