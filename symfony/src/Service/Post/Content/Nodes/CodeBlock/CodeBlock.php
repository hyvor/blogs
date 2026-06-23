<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\CodeBlock;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\CodeHighlight\Highlighter;
use App\Service\Theme\ThemeFilesService;
use App\Service\Delivery\Twig\TwigRendererService;
use DOMDocument;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Converters\HtmlParser\Whitespace;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class CodeBlock extends NodeType
{
    public string $name = 'code_block';
    public string $attrs = CodeBlockAttrs::class;
    public ?string $content = 'text*';
    public string $group = 'block';

    public function __construct(
        private Blog $blog,
        private bool $isPlain,
        private ThemeFilesService $themeFilesService,
        private TwigRendererService $twigRendererService,
        private Highlighter $highlighter,
        private string $projectDir,
    ) {}

    public function toHtml(Node $node, string $children): string
    {
        $blog = $this->blog;
        $isPlain = $this->isPlain;

        $syntaxOn = $blog->getMeta()->syntax_on;
        $lineNumbers = $blog->getMeta()->syntax_line_numbers;
        $themeName = strval($blog->getMeta()->syntax_theme ?? 'nord');

        $code = $node->allText();

        if ($isPlain) {
            $code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        }

        /** @var string $language */
        $language = $node->attr('language') ?? 'plain';
        /** @var string $annotations */
        $annotations = $node->attr('annotations') ?? '';
        /** @var string $fileName */
        $fileName = $node->attr('name') ?? '';

        $pre = [
            'style' => '',
            'class' => '',
            'onmouseenter' => '',
            'onmouseleave' => '',
        ];

        if (!$isPlain && $syntaxOn) {
            [
                'pre' => $pre,
                'code' => $code
            ] = $this->highlighter->highlight(
                code: $code,
                language: $language,
                themeName: $themeName,
                lineNumbers: $lineNumbers,
                annotations: $annotations
            );
        }

        $template = $isPlain ? null : $this->themeFilesService->getFile(
            $blog,
            'block-code.twig',
            ThemeFileFolder::TEMPLATES
        )?->getContent();

        if (!$template) {
            $template = strval(file_get_contents($this->projectDir . '/resources/twig/blocks/code.twig'));
        }

        return $this->twigRendererService->renderString($template, [
            'data' => [
                'pre' => $pre,
                'code' => $code,
                'language' => $language,
                'name' => $fileName,
                'theme' => $themeName,
                'line_numbers' => $lineNumbers,
                'annotations' => $annotations,
            ],
        ]);
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'pre',
                getAttrs: function ($el) {
                    return CodeBlockAttrs::fromArray([
                        'language' => $el->getAttribute('data-language'),
                        'name' => $el->getAttribute('data-name'),
                        'annotations' => $el->getAttribute('data-annotations'),
                    ]);
                },
                getChildren: function ($node) {
                    /** @var DOMDocument $document */
                    $document = $node->ownerDocument;

                    $text = $node->textContent ?? '';
                    $text = trim($text);
                    $text = $text ?: ' ';

                    return $document->createTextNode($text);
                },
                whitespace: Whitespace::PRESERVE
            ),
        ];
    }
}
