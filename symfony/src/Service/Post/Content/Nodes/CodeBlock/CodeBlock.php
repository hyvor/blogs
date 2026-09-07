<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\CodeBlock;

use DOMDocument;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Converters\HtmlParser\Whitespace;
use Hyvor\Phrosemirror\Types\NodeType;

class CodeBlock extends NodeType
{
    public string $name = 'code_block';
    public string $attrs = CodeBlockAttrs::class;
    public ?string $content = 'text*';
    public string $group = 'block';

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
                    $text = $text ?: ' '; // prevents codemirror error when empty

                    return $document->createTextNode($text);
                },
                whitespace: Whitespace::PRESERVE
            ),
        ];
    }
}
