<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\Callout\Callout;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Callout::class)]
class CalloutTest extends KernelTestCase
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
                    'type' => 'callout',
                    'attrs' => [
                        'emoji' => '💡',
                        'bg' => '#fff0f0',
                        'fg' => '#000000',
                    ],
                    'content' => [['type' => 'text', 'text' => 'Note']],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());
        $this->assertSame('<aside style="background-color:#fff0f0;color:#000000"><span>💡</span><div>Note</div></aside>', $html);
    }

    public function test_html_to_json(): void
    {
        $html = '<aside style="background-color:#fff0f0;color:#000000"><span>💡</span><div>Note</div></aside>';
        $json = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'callout',
                    'attrs' => [
                        'emoji' => '💡',
                        'bg' => '#fff0f0',
                        'fg' => '#000000',
                    ],
                    'content' => [
                        ['type' => 'text', 'text' => 'Note'],
                    ],
                ],
            ],
        ]), $json);
    }

    public function test_html_to_json_without_div(): void
    {
        $html = '<aside>Some <b>content</b></aside>';
        $json = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'callout',
                    'attrs' => [
                        'emoji' => Callout::DEFAULT_EMOJI,
                        'bg' => Callout::DEFAULT_BG,
                        'fg' => Callout::DEFAULT_FG,
                    ],
                    'content' => [
                        ['type' => 'text', 'text' => 'Some '],
                        [
                            'type' => 'text',
                            'text' => 'content',
                            'marks' => [
                                ['type' => 'strong'],
                            ],
                        ],
                    ],
                ],
            ],
        ]), $json);
    }
}
