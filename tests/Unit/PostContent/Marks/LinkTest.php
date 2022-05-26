<?php

namespace App\Domains\Post\Content\Marks;

use App\Domains\Post\Content\PostContentRepository;

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
    $result = PostContentRepository::getHtml($this->document,  blog());
    expect($result)->toEqual("<a href=\"$this->link\" target=\"_blank\" rel=\"noopener noreferrer\">$this->text</a>");
});

test('internal link', function () {
    $blog = blog();
    $blog->update([
       'hosting_at' => 'self',
       'hosting_url' => 'https://example.com',
   ]);

    $result = PostContentRepository::getHtml($this->document,  $blog);
    expect($result)->toEqual("<a href=\"$this->link\" rel=\"noopener noreferrer\">$this->text</a>");
});

test('nofollow meta', function () {
    $blog = blog();
    $blog->setMeta('seo_external_links_follow', 'nofollow');
    $result = PostContentRepository::getHtml($this->document,  $blog);
    expect($result)->toEqual("<a href=\"$this->link\" target=\"_blank\" rel=\"noopener noreferrer nofollow\">$this->text</a>");
});
