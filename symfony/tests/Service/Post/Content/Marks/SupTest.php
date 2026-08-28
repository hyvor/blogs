<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Service\Post\Content\Marks\Sup;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\PostSchema;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Sup::class)]
class SupTest extends KernelTestCase
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
                    'text' => 'superscript',
                    'marks' => [['type' => 'sup']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<sup>superscript</sup>', $html);
    }

    public function test_html_to_json(): void
    {
        $document = $this->postSchema()->documentFromHtml('<sup>superscript</sup>', false);

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'superscript',
                    'marks' => [
                        ['type' => 'sup'],
                    ],
                ],
            ],
        ], $document->toArray());
    }
}
