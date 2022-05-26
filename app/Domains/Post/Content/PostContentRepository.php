<?php

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
use App\Domains\Post\Content\Nodes\Bookmark;
use App\Domains\Post\Content\Nodes\BulletList;
use App\Domains\Post\Content\Nodes\Callout;
use App\Domains\Post\Content\Nodes\CodeBlock;
use App\Domains\Post\Content\Nodes\CustomHtml;
use App\Domains\Post\Content\Nodes\Doc;
use App\Domains\Post\Content\Nodes\Embed;
use App\Domains\Post\Content\Nodes\Figcaption;
use App\Domains\Post\Content\Nodes\Figure;
use App\Domains\Post\Content\Nodes\HardBreak;
use App\Domains\Post\Content\Nodes\Heading;
use App\Domains\Post\Content\Nodes\HorizontalRule;
use App\Domains\Post\Content\Nodes\Image;
use App\Domains\Post\Content\Nodes\ListItem;
use App\Domains\Post\Content\Nodes\OrderedList;
use App\Domains\Post\Content\Nodes\Paragraph;
use App\Domains\Post\Content\Nodes\Text;
use App\Models\Blog;
use Tiptap\Editor;

class PostContentRepository
{
    public static function getHtml(array|string $json, Blog $blog)
    {
        return self::getEditor($blog)->setContent($json)->getHTML();
    }

    public static function getText(array|string $json, Blog $blog)
    {
        return self::getEditor($blog)->setContent($json)->getText();
    }

    public static function getJsonFromHtml(string $html, Blog $blog)
    {
        return self::getEditor($blog)->setContent($html)->getJSON();
    }

    private static function getEditor(Blog $blog): Editor
    {
        return new Editor([
            'extensions' => [

                // core
                new Doc(),
                new Text(),

                // nodes
                new Paragraph(),
                new Blockquote(),
                new HorizontalRule(),
                new Heading(),
                new CodeBlock(['blog' => $blog]),
                new CustomHtml(),
                new Figure(),
                new Figcaption(),
                new Image(),
                new Embed(),
                new Callout(['blog' => $blog]),
                new HardBreak(),
                new BulletList(),
                new OrderedList(),
                new ListItem(),
                new Bookmark(['blog' => $blog]),

                // marks
                new Code(),
                new Highlight(),
                new Link(['blog' => $blog]),
                new Strong(),
                new Em(),
                new Strike(),
                new Sub(),
                new Sup(),

            ],
        ]);
    }

    public static function getDefaultBlockTemplate(string $name)
    {
        return file_get_contents(resource_path("twig/blocks/$name.twig"));
    }
}
