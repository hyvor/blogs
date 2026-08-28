<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Service\Post\Content\Marks\Strong;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Strong::class)]
class StrongTest extends KernelTestCase
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
                    'text' => 'bold',
                    'marks' => [['type' => 'strong']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<strong>bold</strong>', $html);
    }

    public function test_b_tag_to_json(): void
    {
        $document = $this->postSchema()->documentFromHtml('<b>bold</b>', false);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'bold',
                    'marks' => [
                        ['type' => 'strong'],
                    ],
                ],
            ],
        ], $document->toArray());
    }

    public function test_strong_tag_to_json(): void
    {
        $document = $this->postSchema()->documentFromHtml('<strong>bold</strong>', false);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'bold',
                    'marks' => [
                        ['type' => 'strong'],
                    ],
                ],
            ],
        ], $document->toArray());
    }
}
