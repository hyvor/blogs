<?php declare(strict_types=1);

namespace App\Domains\Post\Content;

use App\Domains\Post\Content\Marks\Code;
use App\Domains\Post\Content\Marks\Em;
use App\Domains\Post\Content\Marks\Highlight;
use App\Domains\Post\Content\Marks\Link;
use App\Domains\Post\Content\Marks\Strike;
use App\Domains\Post\Content\Marks\Strong;
use App\Domains\Post\Content\Marks\Sub;
use App\Domains\Post\Content\Marks\Sup;
use App\Domains\Post\Content\Nodes\Blockquote;
use App\Domains\Post\Content\Nodes\Bookmark\Bookmark;
use App\Domains\Post\Content\Nodes\BulletList;
use App\Domains\Post\Content\Nodes\Callout\Callout;
use App\Domains\Post\Content\Nodes\CustomHtml;
use App\Domains\Post\Content\Nodes\CodeBlock\CodeBlock;
use App\Domains\Post\Content\Nodes\Embed\Embed;
use App\Domains\Post\Content\Nodes\Doc;
use App\Domains\Post\Content\Nodes\Figcaption;
use App\Domains\Post\Content\Nodes\Figure;
use App\Domains\Post\Content\Nodes\HardBreak;
use App\Domains\Post\Content\Nodes\Heading\Heading;
use App\Domains\Post\Content\Nodes\HorizontalRule;
use App\Domains\Post\Content\Nodes\Image\Image;
use App\Domains\Post\Content\Nodes\ListItem;
use App\Domains\Post\Content\Nodes\OrderedList;
use App\Domains\Post\Content\Nodes\Paragraph;
use App\Domains\Post\Content\Nodes\Text;
use App\Models\Blog;
use App\Models\PostVariant;
use Hyvor\Phrosemirror\Converters\HtmlParser\HtmlParser;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\Schema;

class PostContentService
{

    /**
     * @param array<mixed>|string $json
     */
    public static function getHtml(array|string $json, Blog $blog, PostContentOptions $options = null) : string
    {
        return Document::fromJson(self::getSchema($blog, $options), $json)->toHtml();
    }

    /**
     * @param array<mixed>|string $json
     */
    public static function getText(array|string $json, Blog $blog) : string
    {
        return Document::fromJson(self::getSchema($blog), $json)->toText();
    }

    public static function getJsonFromHtml(string $html, Blog $blog) : string
    {
        return self::getDocumentFromHtml($html, $blog)->toJson();
    }

    public static function getDocumentFromHtml(string $html, Blog $blog) : Node
    {
        $parser = HtmlParser::fromSchema(self::getSchema($blog));
        return $parser->parse($html);
    }

    /**
     * @param array<mixed>|string $json
     */
    public static function getDocumentFromJson(array|string $json, Blog $blog) : Node
    {
        return Document::fromJson(self::getSchema($blog), $json);
    }

    private static function getSchema(Blog $blog, PostContentOptions $options = null) : Schema
    {

        $options ??= new PostContentOptions;

        return new Schema(
            [
                new Doc,
                new Text,
                new Blockquote,
                new Bookmark($blog),
                new BulletList,
                new Callout,
                new CodeBlock($blog, $options->isCodeBlockPlain),
                new CustomHtml,
                new Embed,
                new ListItem,
                new Paragraph,
                new Figcaption,
                new Figure,
                new HardBreak,
                new Heading,
                new HorizontalRule,
                new Image($blog),
                new OrderedList,
            ],
            [
                new Code,
                new Em,
                new Highlight,
                new Link($blog),
                new Strike,
                new Strong,
                new Sub,
                new Sup,
            ]
        );

    }

    public static function getDefaultBlockTemplate(string $name) : string
    {
        return strval(
            file_get_contents(resource_path("twig/blocks/$name.twig"))
        );
    }


}
