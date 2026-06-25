<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Nodes;

use App\Entity\Blog;
use App\Service\Post\Content\Nodes\CodeBlock\CodeBlock;
use App\Service\Post\Content\PostContentOptions;
use App\Service\Post\Content\PostContentService;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeBlock::class)]
class CodeBlockTest extends KernelTestCase
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
        $code = '$x = null';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'code_block',
                    'attrs' => [
                        'language' => 'php',
                        'annotations' => 'h=1',
                        'name' => 'file.js',
                    ],
                    'content' => [['type' => 'text', 'text' => $code]],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog());

        $dom = new \DOMDocument();
        $dom->loadXML($html);

        /** @var \DOMElement $pre */
        $pre = $dom->firstChild;

        $this->assertSame('language-php has-highlight has-line-numbers', $pre->attributes->getNamedItem('class')->value);
        $this->assertSame('h=1', $pre->attributes->getNamedItem('data-annotations')->value);
        $this->assertSame('file.js', $pre->attributes->getNamedItem('data-name')->value);
        $this->assertSame('php', $pre->attributes->getNamedItem('data-language')->value);

        $code = $pre->firstChild;
        $this->assertSame('code', $code->nodeName);
    }

    public function test_json_to_html_plain(): void
    {
        $code = '$x = null';

        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'code_block',
                    'attrs' => ['language' => 'php'],
                    'content' => [['type' => 'text', 'text' => $code]],
                ],
            ],
        ]);

        $html = $this->service()->getHtml($json, $this->blog(), new PostContentOptions(isCodeBlockPlain: true));
        $this->assertStringContainsString('<code>$x = null</code>', $html);
    }

    public function test_html_to_json(): void
    {
        $name = 'app.php';
        $content = "x = null\ny = null";
        $annotations = 'h=1';

        $html = "<pre class=\"language-php\" data-language=\"php\" data-name=\"$name\" data-annotations=\"$annotations\">$content</pre>";
        $json = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'code_block',
                    'attrs' => [
                        'language' => 'php',
                        'name' => $name,
                        'annotations' => $annotations,
                    ],
                    'content' => [
                        ['type' => 'text', 'text' => $content],
                    ],
                ],
            ],
        ]), $json);
    }

    public function test_removes_code_wrapper_from_pre(): void
    {
        $html = "<pre>\n<code>matchLabels:\n    app: nginx\n</code>\n</pre>";
        $json = $this->service()->getJsonFromHtml($html, $this->blog());

        $this->assertSame(json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'code_block',
                    'attrs' => [
                        'language' => '',
                        'name' => '',
                        'annotations' => '',
                    ],
                    'content' => [
                        ['type' => 'text', 'text' => "matchLabels:\n    app: nginx"],
                    ],
                ],
            ],
        ]), $json);
    }
}
