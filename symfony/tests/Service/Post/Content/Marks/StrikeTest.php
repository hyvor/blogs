<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Service\Post\Content\Marks\Strike;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Strike::class)]
class StrikeTest extends KernelTestCase
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
                    'text' => 'strikethrough',
                    'marks' => [['type' => 'strike']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<s>strikethrough</s>', $html);
    }

    public function test_s_tag_to_json(): void
    {
        $document = $this->postSchema()->documentFromHtml('<s>strikethrough</s>', false);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'strikethrough',
                    'marks' => [
                        ['type' => 'strike'],
                    ],
                ],
            ],
        ], $document->toArray());
    }

    public function test_strike_tag_to_json(): void
    {
        $document = $this->postSchema()->documentFromHtml('<strike>strikethrough</strike>', false);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'strikethrough',
                    'marks' => [
                        ['type' => 'strike'],
                    ],
                ],
            ],
        ], $document->toArray());
    }

    public function test_del_tag_to_json(): void
    {
        $document = $this->postSchema()->documentFromHtml('<del>strikethrough</del>', false);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'strikethrough',
                    'marks' => [
                        ['type' => 'strike'],
                    ],
                ],
            ],
        ], $document->toArray());
    }
}
