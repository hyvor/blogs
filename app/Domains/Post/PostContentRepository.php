<?php
namespace App\Domains\Post;

use App\Domains\Post\Prosemirror\Node\Figcaption;
use App\Domains\Post\Prosemirror\Node\Figure;
use App\Domains\Post\Prosemirror\Node\Rich;
use ProseMirrorToHtml\Nodes\Blockquote;
use ProseMirrorToHtml\Nodes\BulletList;
use ProseMirrorToHtml\Nodes\CodeBlock;
use ProseMirrorToHtml\Nodes\Heading;
use ProseMirrorToHtml\Nodes\HorizontalRule;
use ProseMirrorToHtml\Nodes\ListItem;
use ProseMirrorToHtml\Nodes\OrderedList;
use ProseMirrorToHtml\Nodes\Paragraph;
use ProseMirrorToHtml\Nodes\Image;
use ProseMirrorToHtml\Renderer;

class PostContentRepository {

    public static function prosemirrorToHtml(string $content) {
        $json = json_decode($content, true);
        
        $renderer = new Renderer();

        // must be similar to schema.js
        $renderer->withNodes([
            Paragraph::class,
            Blockquote::class,
            HorizontalRule::class,
            Heading::class,
            CodeBlock::class,
            ListItem::class,
            OrderedList::class,
            BulletList::class,
            Image::class,

            // our custom
            Figure::class,
            Figcaption::class,
            Rich::class,
        ]);

        return $renderer->render($json);

    }

}