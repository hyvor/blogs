<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Service\Post\Content\Marks\Highlight;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Highlight::class)]
class HighlightTest extends KernelTestCase
{
    private function service(): PostContentService
    {
        return $this->getService(PostContentService::class);
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
                    'text' => 'highlighted',
                    'marks' => [['type' => 'highlight']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<mark>highlighted</mark>', $html);
    }

    public function test_html_to_json(): void
    {
        $document = $this->service()->getDocumentFromHtml('<mark>highlighted</mark>', $this->blog());

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
                            'text' => 'highlighted',
                            'marks' => [
                                ['type' => 'highlight'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $document->toArray());
    }
}
