<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Service\Post\Content\Marks\Em;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Em::class)]
class EmTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
    }

    private function postSchema(): PostSchema
    {
        return $this->getService(PostSchema::class);
    }

    private function blog(): Blog
    {
        return (new Blog())->setSubdomain('test');
    }

    public function test_json_to_html(): void
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'italic',
                    'marks' => [['type' => 'em']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<em>italic</em>', $html);
    }

    public function test_html_to_json(): void
    {
        $document = $this->postSchema()->documentFromHtml('<em>italic</em>');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'suggestions' => null,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'italic',
                            'marks' => [
                                ['type' => 'em'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $document->toArray());
    }

    public function test_i_tag_to_json(): void
    {
        $document = $this->postSchema()->documentFromHtml('<i>italic</i>');

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'suggestions' => null,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'italic',
                            'marks' => [
                                ['type' => 'em'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $document->toArray());
    }
}
