<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use App\Service\Post\Content\Marks\Code;
use App\Service\Post\Content\Marks\Em;
use App\Service\Post\Content\Marks\Highlight;
use App\Service\Post\Content\Marks\Link;
use App\Service\Post\Content\Marks\Strike;
use App\Service\Post\Content\Marks\Strong;
use App\Service\Post\Content\Marks\Sub;
use App\Service\Post\Content\Marks\Suggestion;
use App\Service\Post\Content\Marks\Sup;
use App\Service\Post\Content\Nodes\Audio\Audio;
use App\Service\Post\Content\Nodes\Blockquote;
use App\Service\Post\Content\Nodes\Bookmark\Bookmark;
use App\Service\Post\Content\Nodes\BulletList;
use App\Service\Post\Content\Nodes\Button\Button;
use App\Service\Post\Content\Nodes\Callout\Callout;
use App\Service\Post\Content\Nodes\CodeBlock\CodeBlock;
use App\Service\Post\Content\Nodes\CustomHtml;
use App\Service\Post\Content\Nodes\Doc;
use App\Service\Post\Content\Nodes\Embed\Embed;
use App\Service\Post\Content\Nodes\Figcaption;
use App\Service\Post\Content\Nodes\Figure;
use App\Service\Post\Content\Nodes\HardBreak;
use App\Service\Post\Content\Nodes\Heading\Heading;
use App\Service\Post\Content\Nodes\HorizontalRule;
use App\Service\Post\Content\Nodes\Image\Image;
use App\Service\Post\Content\Nodes\ListItem;
use App\Service\Post\Content\Nodes\OrderedList;
use App\Service\Post\Content\Nodes\Paragraph;
use App\Service\Post\Content\Nodes\Table\Table;
use App\Service\Post\Content\Nodes\Table\TableCell\TableCell;
use App\Service\Post\Content\Nodes\Table\TableCell\TableHeader;
use App\Service\Post\Content\Nodes\Table\TableRow;
use App\Service\Post\Content\Nodes\Text;
use App\Service\Post\Content\Nodes\Toc\Toc;
use Hyvor\Phrosemirror\Converters\HtmlParser\HtmlParser;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use Hyvor\Phrosemirror\Types\Schema;

class PostSchema
{

    private Schema $schema;

    public function __construct()
    {
        $this->schema = new Schema(
            [
                new Doc(),
                new Text(),
                new Paragraph(),
                new Blockquote(),
                new Toc(),
                new Bookmark(),
                new BulletList(),
                new Callout(),
                new CodeBlock(),
                new CustomHtml(),
                new Embed(),
                new ListItem(),
                new Figcaption(),
                new Figure(),
                new HardBreak(),
                new Heading(),
                new HorizontalRule(),
                new Image(),
                new OrderedList(),
                new Table(),
                new TableRow(),
                new TableCell(),
                new TableHeader(),
                new Audio(),
                new Button(),
            ],
            [
                new Code(),
                new Em(),
                new Highlight(),
                new Link(),
                new Strike(),
                new Strong(),
                new Sub(),
                new Suggestion(),
                new Sup(),
            ]
        );
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @param array<mixed>|string $json
     * @throws PhrosemirrorException
     */
    public function nodeFrom(array|string $json): Node
    {
        return Node::fromJson($this->schema, $json);
    }

    /**
     * @param array<mixed>|string $json
     * @throws PhrosemirrorException
     */
    public function documentFrom(array|string $json): Document
    {
        return Document::fromJson($this->schema, $json);
    }

    public function getHtmlParser(): HtmlParser
    {
        return HtmlParser::fromSchema($this->schema);
    }

    public function documentFromHtml(string $html, bool $sanitize = true): Node
    {
        return $this->getHtmlParser()->parse($html, sanitize: $sanitize);
    }
}
