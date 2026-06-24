<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use App\Entity\Blog;
use App\Service\CodeHighlight\Highlighter;
use App\Service\Delivery\MediaService;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Media\ImageResizeService;
use App\Service\Post\Content\Marks\Code;
use App\Service\Post\Content\Marks\Em;
use App\Service\Post\Content\Marks\Highlight;
use App\Service\Post\Content\Marks\Link;
use App\Service\Post\Content\Marks\Strike;
use App\Service\Post\Content\Marks\Strong;
use App\Service\Post\Content\Marks\Sub;
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
use App\Service\Route\PermalinkService;
use App\Service\Theme\ThemeFilesService;
use Hyvor\Phrosemirror\Converters\HtmlParser\HtmlParser;
use Hyvor\Phrosemirror\Document\Document;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\Schema;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PostContentService
{
    public function __construct(
        private PermalinkService $permalinkService,
        private ThemeFilesService $themeFilesService,
        private TwigRendererService $twigRendererService,
        private Highlighter $highlighter,
        private MediaService $mediaService,
        private ImageResizeService $imageResizeService,
        private UrlDataService $urlDataService,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {}

    /**
     * @param array<mixed>|string $json
     */
    public function getHtml(array|string $json, Blog $blog, ?PostContentOptions $options = null): string
    {
        return Document::fromJson($this->getSchema($blog, $options), $json)->toHtml();
    }

    /**
     * @param array<mixed>|string $json
     */
    public function getText(array|string $json, Blog $blog): string
    {
        return Document::fromJson($this->getSchema($blog), $json)->toText();
    }

    public function getJsonFromHtml(string $html, Blog $blog, bool $sanitize = true): string
    {
        return $this->getDocumentFromHtml($html, $blog, $sanitize)->toJson();
    }

    public function getDocumentFromHtml(string $html, Blog $blog, bool $sanitize = true): Node
    {
        $schema = $this->getSchema($blog);
        $parser = HtmlParser::fromSchema($schema);
        return $parser->parse($html, sanitize: $sanitize);
    }

    /**
     * @param array<mixed>|string $json
     */
    public function getDocumentFromJson(array|string $json, Blog $blog): Document
    {
        return Document::fromJson($this->getSchema($blog), $json);
    }

    private function getSchema(Blog $blog, ?PostContentOptions $options = null): Schema
    {
        $options ??= new PostContentOptions();

        return new Schema(
            [
                new Doc(),
                new Text(),
                new Paragraph(),
                new Blockquote(),
                new Toc($blog, $this->themeFilesService, $this->twigRendererService),
                new Bookmark($blog, $this->urlDataService, $this->themeFilesService, $this->twigRendererService, $this->projectDir),
                new BulletList(),
                new Callout(),
                new CodeBlock($blog, $options->isCodeBlockPlain, $this->themeFilesService, $this->twigRendererService, $this->highlighter, $this->projectDir),
                new CustomHtml(),
                new Embed($this->urlDataService),
                new ListItem(),
                new Figcaption(),
                new Figure(),
                new HardBreak(),
                new Heading($blog),
                new HorizontalRule(),
                new Image($blog, $this->permalinkService, $this->mediaService, $this->imageResizeService),
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
                new Link($blog, $this->permalinkService),
                new Strike(),
                new Strong(),
                new Sub(),
                new Sup(),
            ]
        );
    }
}
