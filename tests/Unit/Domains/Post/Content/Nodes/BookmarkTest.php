<?php

namespace Tests\Unit\PostContent\Nodes;

use App\Data\Enums\ResultEnum;
use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\UrlData;

beforeEach(function () {
    $this->url = 'https://example.com';
    $this->title = 'I am title';
    $this->description = 'I am description';
    $this->thumbnail_url = '/img.png';
    $this->icon_url = '/icon.ico';
    $this->site = 'Youtube';

    // add URL data first
    UrlData::create([
        'result' => ResultEnum::OK,
        'fetch_type' => UrlDataFetchTypeEnum::LINK,
        'url' => $this->url,
        'final_url' => $this->url,
        'title' => $this->title,
        'description' => $this->description,
        'thumbnail_url' => $this->thumbnail_url,
        'icon_url' => $this->icon_url,
        'site' => $this->site,
    ]);
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

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toContain(
        $this->url,
        $this->title,
        $this->description,
    );

    $this->assertStringNotContainsString($this->site, $html);
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

    ThemeFilesRepository::createOrUpdateFile(
        blog(),
        ThemeFileFolderEnum::TEMPLATES,
        'block-bookmark.twig',
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

    $html = PostContentRepository::getHtml($json, blog());

    expect($html)->toContain(
        $this->url,
        $this->title,
        $this->description,
        $this->site,
        $this->thumbnail_url,
        $this->icon_url
    );
});
