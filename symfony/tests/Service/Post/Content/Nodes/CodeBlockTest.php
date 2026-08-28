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
        ], JSON_THROW_ON_ERROR);

        $html = $this->service()->getHtml($json, $this->blog());

        $dom = new \DOMDocument();
        $dom->loadXML($html);

        /** @var \DOMElement $pre */
        $pre = $dom->firstChild;

        $classAttr = $pre->attributes->getNamedItem('class');
        $this->assertNotNull($classAttr);
        $this->assertSame('language-php has-highlight has-line-numbers', $classAttr->value);

        $annotationsAttr = $pre->attributes->getNamedItem('data-annotations');
        $this->assertNotNull($annotationsAttr);
        $this->assertSame('h=1', $annotationsAttr->value);

        $nameAttr = $pre->attributes->getNamedItem('data-name');
        $this->assertNotNull($nameAttr);
        $this->assertSame('file.js', $nameAttr->value);

        $languageAttr = $pre->attributes->getNamedItem('data-language');
        $this->assertNotNull($languageAttr);
        $this->assertSame('php', $languageAttr->value);

        $code = $pre->firstChild;
        $this->assertNotNull($code);
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
        ], JSON_THROW_ON_ERROR);

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
                        'suggestions' => null,
                    ],
                    'content' => [
                        ['type' => 'text', 'text' => $content],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
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
                        'suggestions' => null,
                    ],
                    'content' => [
                        ['type' => 'text', 'text' => "matchLabels:\n    app: nginx"],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR), $json);
    }
}
