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
use App\Domains\Post\Content\Nodes\Bookmark;
use App\Domains\Post\Content\Nodes\BulletList;
use App\Domains\Post\Content\Nodes\Callout;
use App\Domains\Post\Content\Nodes\Doc;
use App\Domains\Post\Content\Nodes\ListItem;
use App\Domains\Post\Content\Nodes\Paragraph;
use App\Domains\Post\Content\Nodes\Text;
use App\Models\Blog;
use App\Models\PostVariant;
use Faker\Factory;
use Hyvor\Phrosemirror\Converters\HtmlParser\HtmlParser;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\Schema;
use Tiptap\Editor;

/**
 * @phpstan-type EditorOptions array{code_block_is_plain?: bool}
 */
class PostContentRepository
{

    /**
     * @param array<mixed>|string $json
     * @param EditorOptions $options
     */
    public static function getHtml(array|string $json, Blog $blog, array $options = []) : string
    {
        return Document::fromJson(self::getSchema($blog), $json)->toHtml();
    }

    /**
     * @param array<mixed>|string $json
     */
    public static function getText(array|string $json, Blog $blog) : string
    {
        return self::getEditor($blog)->setContent($json)->getText();
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

    private static function getSchema(Blog $blog) : Schema
    {

        return new Schema(
            [
                new Doc,
                new Text,
                new Blockquote,
                new Bookmark($blog),
                new BulletList,
                new Callout,
                new ListItem,
                new Paragraph,
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

    /**
     * @param EditorOptions $options
     */
    private static function getEditor(Blog $blog, array $options = []): Editor
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
                new CodeBlock([
                    'blog' => $blog,
                    'is_plain' => $options['code_block_is_plain'] ?? false
                ]),
                new CustomHtml(),
                new Figure(),
                new Figcaption(),
                new Image(['blog' => $blog]),
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

    public static function getDefaultBlockTemplate(string $name) : string
    {
        return strval(
            file_get_contents(resource_path("twig/blocks/$name.twig"))
        );
    }

    public function updateVariantHtml(PostVariant $variant)
    {
        if (!$variant->content) {
            return;
        }

        $post = $variant->post;
        $blog = $post->blog;
        $html = PostContentRepository::getHtml($variant->content, $blog);

        $variant->content_html = $html;
        $variant->save();
    }

    public static function generateRandom()
    {
        $faker = Factory::create();
        $paragraphs = $faker->paragraphs(rand(2, 6));
        $content = [
            'type' => 'doc',
            'content' => [],
        ];
        foreach ($paragraphs as $para) {
            $content['content'][] = [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => $para,
                ]],
            ];
        }

        return json_encode($content);
    }

    public static function generateParagraph(string $text = null) : string
    {

        $content = [
            'type' => 'doc',
            'content' => [],
        ];
        $content['content'][] = [
            'type' => 'paragraph',
            'content' => [[
                'type' => 'text',
                'text' => $text ?? Factory::create()->paragraph,
            ]],
        ];

        return strval(json_encode($content));

    }

}
