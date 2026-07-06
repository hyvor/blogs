<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Entity\Enum\Blog\SeoExternalLinksFollow;
use App\Entity\Enum\BlogHostingAt;
use App\Entity\Meta\BlogMeta;
use App\Service\Post\Content\Marks\Link;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Link::class)]
class LinkTest extends KernelTestCase
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
                        'attrs' => ['href' => 'https://example.com/page'],
                    ],
                ],
            ],
        ],
    ];

    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        $blog = new Blog();
        $blog->setSubdomain('test');
        return $blog;
    }

    public function test_json_to_html_external(): void
    {
        $result = $this->service()->getHtml(self::DEFAULT_DOC, $this->blog());
        $this->assertSame(
            '<a href="https://example.com/page" target="_blank" rel="noopener noreferrer">Example Text</a>',
            $result
        );
    }

    public function test_internal_link(): void
    {
        $blog = (new Blog())->setSubdomain('test');
        $blog->setHostingAt(BlogHostingAt::SELF);
        $blog->setHostingUrl('https://example.com');

        $result = $this->service()->getHtml(self::DEFAULT_DOC, $blog);
        $this->assertSame(
            '<a href="https://example.com/page" rel="noopener noreferrer">Example Text</a>',
            $result
        );
    }

    public function test_nofollow_meta(): void
    {
        $blog = (new Blog())->setSubdomain('test');
        $meta = new BlogMeta();
        $meta->seo_external_links_follow = SeoExternalLinksFollow::NOFOLLOW;
        $blog->setMeta($meta);

        $result = $this->service()->getHtml(self::DEFAULT_DOC, $blog);
        $this->assertSame(
            '<a href="https://example.com/page" target="_blank" rel="noopener noreferrer nofollow">Example Text</a>',
            $result
        );
    }

    public function test_html_to_json(): void
    {
        $result = $this->service()->getDocumentFromHtml('<a href="https://example.com">Example Text</a>', $this->blog(), false);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'Example Text',
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => ['href' => 'https://example.com'],
                        ],
                    ],
                ],
            ],
        ], $result->toArray());
    }
}
