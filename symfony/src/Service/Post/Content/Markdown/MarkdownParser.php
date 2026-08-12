<?php declare(strict_types=1);

namespace App\Service\Post\Content\Markdown;

use App\Service\Post\Content\Markdown\CommonMarkExt\Superscript;
use App\Service\Post\Content\Markdown\CommonMarkExt\SuperscriptDelimiterProcessor;
use App\Service\Post\Content\PostSchema;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code as CodeInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image as ImageInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\Highlight\HighlightExtension;
use League\CommonMark\Extension\Highlight\Mark as HighlightInline;
use League\CommonMark\Extension\Strikethrough\Strikethrough;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\Table as TableBlock;
use League\CommonMark\Extension\Table\TableCell as TableCellBlock;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text as TextInline;
use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Parser\MarkdownParser as CommonMarkParser;

class MarkdownParser
{

    /**
     * @throws PhrosemirrorException
     * @throws CommonMarkException
     * @return Node[]
     */
    public function parse(string $markdown): array
    {
        $environment = new Environment([
            'html_input' => 'allow',
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new HighlightExtension());
        $environment->addExtension(new TableExtension());
        $environment->addDelimiterProcessor(new SuperscriptDelimiterProcessor());

        $parser = new CommonMarkParser($environment);
        $document = $parser->parse($markdown);

        $blocks = $this->convertBlocks($document->children());

        $schema = new PostSchema();
        $ret = [];
        foreach ($blocks as $block) {
            $ret[] = $schema->nodeFrom($block);
        }
        return $ret;
    }

    /**
     * @param iterable<CommonMarkNode> $blocks
     * @return array<int, array<string, mixed>>
     */
    private function convertBlocks(iterable $blocks): array
    {
        $nodes = [];

        foreach ($blocks as $block) {
            $node = $this->convertBlock($block);
            if ($node !== null) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function convertBlock(CommonMarkNode $block): ?array
    {
        return match (true) {
            $block instanceof Paragraph => $this->convertParagraph($block),
            $block instanceof Heading => $this->convertHeading($block),
            $block instanceof BlockQuote => $this->convertBlockquote($block),
            $block instanceof ThematicBreak => ['type' => 'horizontal_rule'],
            $block instanceof ListBlock => [
                'type' => $block->getListData()->type === ListBlock::TYPE_ORDERED ? 'ordered_list' : 'bullet_list',
                'content' => $this->convertBlocks($block->children()),
            ],
            $block instanceof ListItem => [
                'type' => 'list_item',
                'content' => $this->convertBlocks($block->children()),
            ],
            $block instanceof FencedCode => $this->convertCodeBlock($block->getInfoWords()[0] ?? null, $block->getLiteral()),
            $block instanceof IndentedCode => $this->convertCodeBlock(null, $block->getLiteral()),
            $block instanceof TableBlock => $this->convertTable($block),
            $block instanceof HtmlBlock => $this->convertHtmlBlock($block),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function convertParagraph(Paragraph $paragraph): array
    {
        $children = $this->toArray($paragraph->children());

        if (count($children) === 1 && $children[0] instanceof ImageInline) {
            $special = $this->convertSpecialImageParagraph($children[0]);
            if ($special !== null) {
                return $special;
            }

            return $this->convertImageParagraph($children[0]);
        }

        return [
            'type' => 'paragraph',
            'content' => $this->convertInlines($children),
        ];
    }

    /**
     * MarkdownSerializer appends "{#id}" after a heading's text when it has
     * an id attr (see MarkdownSerializer::headingToMarkdown()).
     *
     * @return array<string, mixed>
     */
    private function convertHeading(Heading $heading): array
    {
        $content = $this->convertInlines($heading->children());
        $id = null;

        $lastIndex = count($content) - 1;
        if ($lastIndex >= 0 && ($content[$lastIndex]['type'] ?? null) === 'text' && !isset($content[$lastIndex]['marks'])) {
            $text = $content[$lastIndex]['text'];
            if (is_string($text) && preg_match('/^(.*?)\s*\{#([\w-]+)}$/', $text, $matches)) {
                $id = $matches[2];
                if ($matches[1] === '') {
                    array_pop($content);
                } else {
                    $content[$lastIndex]['text'] = $matches[1];
                }
            }
        }

        $attrs = ['level' => $heading->getLevel()];
        if ($id !== null) {
            $attrs['id'] = $id;
        }

        return [
            'type' => 'heading',
            'attrs' => $attrs,
            'content' => $content,
        ];
    }

    /**
     * Handles the standalone-image markdown MarkdownSerializer emits for
     * audio/bookmark/embed/toc/button nodes, e.g. "![#audio](https://...)".
     * These use image syntax (rather than a plain link) specifically to
     * avoid CommonMark's shortcut reference-link resolution, which a bare
     * "[#toc]"-style link is otherwise subject to.
     *
     * @return array<string, mixed>|null
     */
    private function convertSpecialImageParagraph(ImageInline $image): ?array
    {
        $children = $this->toArray($image->children());
        $label = $this->plainText($children);
        $url = $image->getUrl();

        // "#button "<label>"" (see MarkdownSerializer::buttonToMarkdown()); the label
        // can contain marks, so it's extracted from $children, not this flattened $label
        if (str_starts_with($label, '#button "') && str_ends_with($label, '"') && mb_strlen($label) >= 10) {
            return $this->convertButtonImage($children, $url);
        }

        return match ($label) {
            '#toc' => ['type' => 'toc'],
            '#audio' => ['type' => 'audio', 'attrs' => ['src' => $url]],
            '#bookmark' => ['type' => 'bookmark', 'attrs' => ['url' => $url]],
            '#embed' => ['type' => 'figure', 'content' => [['type' => 'embed', 'attrs' => ['url' => $url]]]],
            default => null,
        };
    }

    /**
     * Strips the "#button "" marker text from the first/last (always
     * unmarked) text runs, keeping any marks in between intact.
     *
     * @param CommonMarkNode[] $children
     * @return array<string, mixed>
     */
    private function convertButtonImage(array $children, string $href): array
    {
        $content = $this->convertInlines($children);

        if (($content[0]['type'] ?? null) === 'text' && !isset($content[0]['marks']) && is_string($content[0]['text'])) {
            $content[0]['text'] = preg_replace('/^#button "/', '', $content[0]['text'], 1);
        }

        $lastIndex = count($content) - 1;
        if (
            ($content[$lastIndex]['type'] ?? null) === 'text'
            && !isset($content[$lastIndex]['marks'])
            && is_string($content[$lastIndex]['text'])
        ) {
            $content[$lastIndex]['text'] = preg_replace('/"$/', '', $content[$lastIndex]['text'], 1);
        }

        if (($content[0]['text'] ?? null) === '') {
            array_shift($content);
        }

        $lastIndex = count($content) - 1;
        if ($lastIndex >= 0 && ($content[$lastIndex]['text'] ?? null) === '') {
            array_pop($content);
        }

        if ($content === []) {
            $content = [['type' => 'text', 'text' => '']];
        }

        return [
            'type' => 'button',
            'attrs' => ['href' => $href],
            'content' => $content,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertImageParagraph(ImageInline $image): array
    {
        $attrs = ['src' => $image->getUrl()];

        $alt = $this->plainText($image->children());
        if ($alt !== '') {
            $attrs['alt'] = $alt;
        }

        $title = $image->getTitle();
        if ($title !== null && preg_match('/^(\d*)x(\d*)$/', $title, $matches) && ($matches[1] !== '' || $matches[2] !== '')) {
            if ($matches[1] !== '') {
                $attrs['width'] = (int) $matches[1];
            }
            if ($matches[2] !== '') {
                $attrs['height'] = (int) $matches[2];
            }
        }

        return [
            'type' => 'figure',
            'content' => [['type' => 'image', 'attrs' => $attrs]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertBlockquote(BlockQuote $quote): array
    {
        $callout = $this->convertCalloutBlockquote($quote);
        if ($callout !== null) {
            return $callout;
        }

        return [
            'type' => 'blockquote',
            'content' => $this->convertBlocks($quote->children()),
        ];
    }

    /**
     * A callout is serialized as a blockquote whose first line is
     * "[emoji, fg=..., bg=...]" (see MarkdownSerializer::calloutToMarkdown).
     *
     * @return array<string, mixed>|null
     */
    private function convertCalloutBlockquote(BlockQuote $quote): ?array
    {
        $children = $this->toArray($quote->children());

        if (count($children) !== 1 || !$children[0] instanceof Paragraph) {
            return null;
        }

        $inline = $this->toArray($children[0]->children());

        if (
            count($inline) === 0
            || !$inline[0] instanceof TextInline
            || !preg_match('/^\[(.*), fg=(.*), bg=(.*)]$/', $inline[0]->getLiteral(), $matches)
        ) {
            return null;
        }

        $rest = array_slice($inline, 1);
        if (isset($rest[0]) && $rest[0] instanceof Newline) {
            array_shift($rest);
        }

        return [
            'type' => 'callout',
            'attrs' => ['emoji' => $matches[1], 'fg' => $matches[2], 'bg' => $matches[3]],
            'content' => $this->convertInlines($rest),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertCodeBlock(?string $language, string $literal): array
    {
        $text = rtrim($literal, "\n");

        return [
            'type' => 'code_block',
            'attrs' => $language ? ['language' => $language] : [],
            'content' => $text === '' ? [] : [['type' => 'text', 'text' => $text]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertHtmlBlock(HtmlBlock $block): array
    {
        $html = trim($block->getLiteral());

        return [
            'type' => 'custom_html',
            'content' => $html === '' ? [] : [['type' => 'text', 'text' => $html]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertTable(TableBlock $table): array
    {
        $rows = [];

        foreach ($table->children() as $section) {
            if (!$section instanceof TableSection) {
                continue;
            }

            foreach ($section->children() as $row) {
                $cells = [];

                foreach ($row->children() as $cell) {
                    if (!$cell instanceof TableCellBlock) {
                        continue;
                    }

                    $cells[] = [
                        'type' => $cell->getType() === TableCellBlock::TYPE_HEADER ? 'table_header' : 'table_cell',
                        'content' => [[
                            'type' => 'paragraph',
                            'content' => $this->convertInlines($cell->children()),
                        ]],
                    ];
                }

                $rows[] = ['type' => 'table_row', 'content' => $cells];
            }
        }

        return ['type' => 'table', 'content' => $rows];
    }

    /**
     * @param iterable<CommonMarkNode> $inlines
     * @param array<int, array<string, mixed>> $marks
     * @return array<int, array<string, mixed>>
     */
    private function convertInlines(iterable $inlines, array $marks = []): array
    {
        $nodes = [];

        foreach ($inlines as $inline) {
            array_push($nodes, ...$this->convertInline($inline, $marks));
        }

        return $nodes;
    }

    /**
     * @param array<int, array<string, mixed>> $marks
     * @return array<int, array<string, mixed>>
     */
    private function convertInline(CommonMarkNode $inline, array $marks): array
    {
        return match (true) {
            $inline instanceof TextInline => [$this->textNode($inline->getLiteral(), $marks)],
            $inline instanceof CodeInline => [$this->textNode($inline->getLiteral(), [...$marks, ['type' => 'code']])],
            $inline instanceof Strong => $this->convertInlines($inline->children(), [...$marks, ['type' => 'strong']]),
            $inline instanceof Emphasis => $this->convertInlines($inline->children(), [...$marks, ['type' => 'em']]),
            $inline instanceof HighlightInline => $this->convertInlines($inline->children(), [...$marks, ['type' => 'highlight']]),
            $inline instanceof Superscript => $this->convertInlines($inline->children(), [...$marks, ['type' => 'sup']]),
            $inline instanceof Strikethrough => $this->convertInlines(
                $inline->children(),
                [...$marks, ['type' => mb_strlen($inline->getOpeningDelimiter()) >= 2 ? 'strike' : 'sub']]
            ),
            $inline instanceof LinkInline => $this->convertInlines(
                $inline->children(),
                [...$marks, ['type' => 'link', 'attrs' => ['href' => $inline->getUrl()]]]
            ),
            // MarkdownSerializer only ever emits a bare "\n" inside a paragraph for hard_break nodes
            // (paragraphs are otherwise separated by a blank line), so any newline here means hard_break
            $inline instanceof Newline => [['type' => 'hard_break']],
            $inline instanceof HtmlInline => [$this->textNode($inline->getLiteral(), $marks)],
            default => [],
        };
    }

    /**
     * @param array<int, array<string, mixed>> $marks
     * @return array<string, mixed>
     */
    private function textNode(string $text, array $marks): array
    {
        $node = ['type' => 'text', 'text' => $text];

        if ($marks !== []) {
            $node['marks'] = $marks;
        }

        return $node;
    }

    /**
     * @param iterable<CommonMarkNode> $inlines
     */
    private function plainText(iterable $inlines): string
    {
        $text = '';

        foreach ($inlines as $inline) {
            if ($inline instanceof TextInline) {
                $text .= $inline->getLiteral();
            } else {
                $text .= $this->plainText($inline->children());
            }
        }

        return $text;
    }

    /**
     * @param iterable<CommonMarkNode> $nodes
     * @return CommonMarkNode[]
     */
    private function toArray(iterable $nodes): array
    {
        return is_array($nodes) ? $nodes : iterator_to_array($nodes);
    }

}
