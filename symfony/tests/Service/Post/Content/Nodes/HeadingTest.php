<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Entity\Meta\BlogMeta;
use App\Service\Post\Content\Nodes\Heading\Heading;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Heading::class)]
class HeadingTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    public function test_json_to_html_with_id(): void
    {
        foreach (range(1, 6) as $i) {
            $content = "I am a h$i";
            $id = 'custom-id';

            $json = json_encode([
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => $i, 'id' => $id],
                        'content' => [['type' => 'text', 'text' => $content]],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            $html = $this->service()->getHtml($json, $this->blog());
            $this->assertSame("<h$i id=\"$id\"><a href=\"#$id\">$content</a></h$i>", $html);
        }
    }

    public function test_json_to_html_without_id(): void
    {
        foreach (range(1, 6) as $i) {
            $content = "I am a h$i";

            $json = json_encode([
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => $i],
                        'content' => [['type' => 'text', 'text' => $content]],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            $html = $this->service()->getHtml($json, $this->blog());
            $this->assertSame("<h$i>$content</h$i>", $html);
        }
    }

    public function test_h7_becomes_h2(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 7],
                    'content' => [['type' => 'text', 'text' => '']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<h2></h2>', $html);
    }

    public function test_heading_anchors_disabled(): void
    {
        $blog = (new Blog())->setSubdomain('test');
        $meta = new BlogMeta();
        $meta->heading_anchors = false;
        $blog->setMeta($meta);

        foreach (range(1, 6) as $i) {
            $content = "I am a h$i";
            $id = 'custom-id';

            $json = json_encode([
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => $i, 'id' => $id],
                        'content' => [['type' => 'text', 'text' => $content]],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            $html = $this->service()->getHtml($json, $blog);
            $this->assertSame("<h$i id=\"$id\">$content</h$i>", $html);
        }
    }

    public function test_does_not_add_anchor_when_link_inside(): void
    {
        $id = 'custom-id';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2, 'id' => $id],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Linked',
                            'marks' => [
                                [
                                    'type' => 'link',
                                    'attrs' => ['href' => 'https://example.com'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame(
            "<h2 id=\"$id\"><a href=\"https://example.com\" target=\"_blank\" rel=\"noopener noreferrer\">Linked</a></h2>",
            $html
        );
    }

    public function test_html_to_json(): void
    {
        $content = 'A h2';
        $id = 'custom-id';

        $json = $this->service()->getJsonFromHtml("<h2 id=\"$id\">$content</h2>", $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2, 'id' => $id, 'suggestions' => null],
                    'content' => [['type' => 'text', 'text' => $content]],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
