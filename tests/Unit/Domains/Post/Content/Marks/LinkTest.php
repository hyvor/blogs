<?php

namespace App\Domains\Post\Content\_Marks;

use App\Domains\Post\Content\PostContentService;

beforeEach(function () {
    $this->link = 'https://example.com/page';
    $this->text = 'Example Text';
    $this->document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => $this->text,
                'marks' => [
                    [
                        'type' => 'link',
                        'attrs' => [
                            'href' => $this->link,
                        ],
                    ],
                ],
            ],
        ],
    ];
});

test('code JSON to HTML', function () {
    $result = PostContentService::getHtml($this->document, blog());
    expect($result)->toEqual("<a href=\"$this->link\" target=\"_blank\" rel=\"noopener noreferrer\">$this->text</a>");
});

test('internal link', function () {
    $blog = blog();
    $blog->update([
        'hosting_at' => 'self',
        'hosting_url' => 'https://example.com',
    ]);

    $result = PostContentService::getHtml($this->document, $blog);
    expect($result)->toEqual("<a href=\"$this->link\" rel=\"noopener noreferrer\">$this->text</a>");
});

test('nofollow meta', function () {
    $blog = blog();
    $blog->setMeta('seo_external_links_follow', 'nofollow');
    $result = PostContentService::getHtml($this->document, $blog);
    expect($result)->toEqual("<a href=\"$this->link\" target=\"_blank\" rel=\"noopener noreferrer nofollow\">$this->text</a>");
});

test('from HTML', function() {
    $html = '<a href="https://exmaple.com">Example Text</a>';

    $result = PostContentService::getDocumentFromHtml($html, blog());

    expect($result->toArray())->toEqual([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'link',
                        'attrs' => [
                            'href' => 'https://exmaple.com'
                        ]
                    ],
                ],
            ],
        ],
    ]);
});
