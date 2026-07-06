<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Service\Post\Content\Marks\Code;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Code::class)]
class CodeTest extends KernelTestCase
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
                    'text' => 'var x',
                    'marks' => [['type' => 'code']],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<code>var x</code>', $html);
    }

    public function test_html_to_json(): void
    {
        $document = $this->service()->getDocumentFromHtml('<code>var x</code>', $this->blog());

        $this->assertSame([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'var x',
                            'marks' => [
                                ['type' => 'code'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $document->toArray());
    }
}
