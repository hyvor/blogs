<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\CodeBlock;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use DOMDocument;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;
use Hyvor\SyntaxHighlighter\Highlighter;

class CodeBlock extends NodeType
{

    public string $name = 'code_block';
    public string $attrs = CodeBlockAttrs::class;

    public ?string $content = 'text*';
    public string $group = 'block';

    public function __construct(
        private Blog $blog,
        private bool $isPlain = false,
    ) {}

    public function toHtml(Node $node, string $children): string
    {

        $blog = $this->blog;
        $isPlain = $this->isPlain;

        $syntaxOn = $blog->getMeta('syntax_on');
        $lineNumbers = (bool) $blog->getMeta('syntax_line_numbers');
        $themeName = strval($blog->getMeta('syntax_theme') ?? 'nord');

        $code = $node->allText();

        $language = strval($node->attr('language') ?? 'plain');
        $annotations = strval($node->attr('annotations') ?? '');
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
            ] = Highlighter::highlight(
                code: $code,
                language: $language,
                themeName: $themeName,
                lineNumbers: $lineNumbers,
                annotations: $annotations
            );
        }

        $template = $isPlain ? null : ThemeFilesRepository::getFile(
            $blog,
            'block-code.twig',
            ThemeFileFolderEnum::TEMPLATES
        )?->content;

        if (! $template) {
            $template = PostContentService::getDefaultBlockTemplate('code');
        }

        return TwigRenderer::renderString($template, [
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
                    return $document->createTextNode($node->textContent);
                }
            )
        ];

    }

}