<?php

namespace Tests\Unit\Domains\Post\Content\Marks;

use App\Domains\Post\Content\PostContentService;
use Database\Factories\BlogFactory;
use Tests\Case\DatabaseTestCase;

class LinkTest extends DatabaseTestCase
{

    private const array DEFAULT_DOC = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Example Text',
                'marks' => [
                    [
                        'type' => 'link',
                        'attrs' => [
                            'href' => 'https://example.com/page',
                        ],
                    ],
                ],
            ],
        ],
    ];

    public function test_json_to_html(): void
    {
        $result = PostContentService::getHtml(self::DEFAULT_DOC, BlogFactory::one());
        $this->assertSame(
            "<a href=\"https://example.com/page\" target=\"_blank\" rel=\"noopener noreferrer\">Example Text</a>",
            $result
        );
    }

    public function test_internal_link(): void
    {
        $blog = BlogFactory::one();
        $blog->update([
            'hosting_at' => 'self',
            'hosting_url' => 'https://example.com',
        ]);

        $result = PostContentService::getHtml(self::DEFAULT_DOC, $blog);
        $this->assertSame(
            "<a href=\"https://example.com/page\" rel=\"noopener noreferrer\">Example Text</a>",
            $result
        );
    }

    public function test_nofollow_meta(): void
    {
        $blog = BlogFactory::one();
        $blog->setMeta('seo_external_links_follow', 'nofollow');
        $result = PostContentService::getHtml(self::DEFAULT_DOC, $blog);
        $this->assertSame(
            "<a href=\"https://example.com/page\" target=\"_blank\" rel=\"noopener noreferrer nofollow\">Example Text</a>",
            $result
        );
    }

    public function test_from_html(): void
    {
        $html = '<a href="https://exmaple.com">Example Text</a>';
        $result = PostContentService::getDocumentFromHtml($html, BlogFactory::one(), false);

        $this->assertSame([
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
        ], $result->toArray());
    }

}
