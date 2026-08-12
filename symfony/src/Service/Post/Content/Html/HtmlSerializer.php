<?php declare(strict_types=1);

namespace App\Service\Post\Content\Html;

use App\Entity\Blog;
use App\Entity\Enum\Blog\SeoExternalLinksFollow;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\CodeHighlight\Highlighter;
use App\Service\Delivery\MimeTypes;
use App\Service\Delivery\Twig\TwigRendererService;
use App\Service\Media\ImageResizeService;
use App\Service\Media\MediaService;
use App\Service\Post\Content\Marks;
use App\Service\Post\Content\Nodes;
use App\Service\Post\Content\Nodes\Toc\TocHtml;
use App\Service\Post\Content\PostContentOptions;
use App\Service\Route\PermalinkService;
use App\Service\Theme\ThemeFilesService;
use App\Service\UrlData\UrlDataService;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Document\TextNode;
use Hyvor\Unfold\Exception\UnfoldException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class HtmlSerializer
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
    ) {
    }

    public function serialize(Node $node, ?Blog $blog, ?PostContentOptions $options = null, ?Node $topNode = null): string
    {
        $topNode ??= $node;
        $options ??= new PostContentOptions();

        if ($node->type->isText() && $node instanceof TextNode) {
            $content = $node->getSafeText();
        } else {
            $children = '';
            foreach ($node->content->all() as $child) {
                $children .= $this->serialize($child, $blog, $options, $topNode);
            }
            $content = $this->nodeToHtml($node, $children, $blog, $options, $topNode);
        }

        foreach (array_reverse($node->marks) as $mark) {
            $content = $this->markToHtml($mark, $content, $blog);
        }

        return $content;
    }

    private function nodeToHtml(Node $node, string $children, ?Blog $blog, PostContentOptions $options, Node $topNode): string
    {
        return match (true) {
            $node->type instanceof Nodes\Paragraph => "<p>$children</p>",
            $node->type instanceof Nodes\Blockquote => "<blockquote>$children</blockquote>",
            $node->type instanceof Nodes\BulletList => "<ul>$children</ul>",
            $node->type instanceof Nodes\OrderedList => "<ol>$children</ol>",
            $node->type instanceof Nodes\ListItem => "<li>$children</li>",
            $node->type instanceof Nodes\HardBreak => '<br>',
            $node->type instanceof Nodes\HorizontalRule => '<hr>',
            $node->type instanceof Nodes\Figure => "<figure>$children</figure>",
            $node->type instanceof Nodes\Figcaption => "<figcaption>$children</figcaption>",
            $node->type instanceof Nodes\CustomHtml => $this->customHtmlToHtml($node),
            $node->type instanceof Nodes\Callout\Callout => $this->calloutToHtml($node, $children),
            $node->type instanceof Nodes\CodeBlock\CodeBlock => $this->codeBlockToHtml($node, $blog, $options),
            $node->type instanceof Nodes\Heading\Heading => $this->headingToHtml($node, $children, $blog),
            $node->type instanceof Nodes\Toc\Toc => $this->tocToHtml($node, $blog, $topNode),
            $node->type instanceof Nodes\Embed\Embed => $this->embedToHtml($node),
            $node->type instanceof Nodes\Audio\Audio => $this->audioToHtml($node),
            $node->type instanceof Nodes\Button\Button => $this->buttonToHtml($node, $children),
            $node->type instanceof Nodes\Bookmark\Bookmark => $this->bookmarkToHtml($node, $blog),
            $node->type instanceof Nodes\Image\Image => $this->imageToHtml($node, $blog),
            $node->type instanceof Nodes\Table\Table => "<div class=\"table-container\"><table>$children</table></div>",
            $node->type instanceof Nodes\Table\TableRow => "<tr>$children</tr>",
            $node->type instanceof Nodes\Table\TableCell\TableCellBase => $this->tableCellToHtml($node, $children),
            default => $children,
        };
    }

    private function markToHtml(Mark $mark, string $children, ?Blog $blog): string
    {
        return match (true) {
            $mark->type instanceof Marks\Code => "<code>$children</code>",
            $mark->type instanceof Marks\Em => "<em>$children</em>",
            $mark->type instanceof Marks\Strong => "<strong>$children</strong>",
            $mark->type instanceof Marks\Strike => "<s>$children</s>",
            $mark->type instanceof Marks\Sub => "<sub>$children</sub>",
            $mark->type instanceof Marks\Sup => "<sup>$children</sup>",
            $mark->type instanceof Marks\Highlight => "<mark>$children</mark>",
            $mark->type instanceof Marks\Link => $this->linkToHtml($mark, $children, $blog),
            default => $children,
        };
    }

    private function customHtmlToHtml(Node $node): string
    {
        $code = $node->allText();
        return "<p>$code</p>";
    }

    private function calloutToHtml(Node $node, string $children): string
    {
        /** @var string $bg */
        $bg = $node->attr('bg');
        /** @var string $fg */
        $fg = $node->attr('fg');
        /** @var string $emoji */
        $emoji = $node->attr('emoji');

        return "<aside style=\"background-color:$bg;color:$fg\"><span>$emoji</span><div>$children</div></aside>";
    }

    private function codeBlockToHtml(Node $node, ?Blog $blog, PostContentOptions $options): string
    {
        if (!$blog) {
            return '';
        }

        $isPlain = $options->isCodeBlockPlain;

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

    private function headingToHtml(Node $node, string $children, ?Blog $blog): string
    {
        /** @var int|string $rawLevel */
        $rawLevel = $node->attr('level');
        $level = intval($rawLevel);
        $level = in_array($level, Nodes\Heading\Heading::ALLOWED_LEVELS) ? $level : 2;

        /** @var ?string $id */
        $id = $node->attr('id');
        $idAttr = $id ? " id=\"$id\"" : null;

        if (
            $id &&
            $blog &&
            !preg_match('/<a\b[^>]*>.*<\/a>/', $children) &&
            $blog->getMeta()->heading_anchors
        ) {
            $children = "<a href=\"#$id\">$children</a>";
        }

        return "<h$level$idAttr>$children</h$level>";
    }

    private function tocToHtml(Node $node, ?Blog $blog, Node $topNode): string
    {
        if (!$blog) {
            return '';
        }

        $template = $this->themeFilesService->getFile(
            $blog,
            'node-toc.twig',
            ThemeFileFolder::TEMPLATES
        )?->getContent();

        if (!$template) {
            $template = '{{ toc | raw }}';
        }

        /** @var int[] $levels */
        $levels = $node->attrs->get('levels') ?: Nodes\Toc\Toc::DEFAULT_LEVELS;
        $tocHtml = new TocHtml($levels);
        $toc = $tocHtml->htmlFromNode($topNode);

        return $this->twigRendererService->renderString($template, [
            'toc' => $toc,
        ]);
    }

    private function embedToHtml(Node $node): string
    {
        /** @var string $url */
        $url = $node->attr('url') ?? '';
        $embedContent = null;

        if ($url) {
            try {
                $embedContent = $this->urlDataService->getEmbed($url) ?: null;
            } catch (\Exception $e) {
                $errorMessage = $e instanceof UnfoldException ? $e->getMessage() : 'unknown error';
                $embedContent = '<!-- Unable to fetch embed for URL: ' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '. Error: ' . htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') . ' -->';
            }
        }

        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return $embedContent !== null ? '<x-embed data-url="' . $safeUrl . '">' . $embedContent . '</x-embed>' : '';
    }

    private function audioToHtml(Node $node): string
    {
        /** @var string $src */
        $src = $node->attr('src');
        return '<audio controls src="' . $src . '"></audio>';
    }

    private function buttonToHtml(Node $node, string $children): string
    {
        /** @var string $href */
        $href = $node->attr('href');
        /** @var string $text */
        $text = $node->attr('text');

        if (empty($text)) {
            $text = $children;
        }

        return "<p class=\"button-wrap\"><a href=\"$href\" target=\"_blank\" class=\"button\">$text</a></p>";
    }

    private function bookmarkToHtml(Node $node, ?Blog $blog): string
    {
        if (!$blog) {
            return '';
        }

        /** @var string $url */
        $url = $node->attr('url');

        if (!$url) {
            return '';
        }

        try {
            $data = $this->urlDataService->getLink((string) $url);
        } catch (UnfoldException) {
            return '';
        }

        $template = $this->themeFilesService->getFile(
            $blog,
            'node-bookmark.twig',
            ThemeFileFolder::TEMPLATES
        )?->getContent();

        if (!$template) {
            $template = strval(file_get_contents($this->projectDir . '/resources/twig/blocks/bookmark.twig'));
        }

        return $this->twigRendererService->renderString($template, [
            // this object is a little different to support the historical bookmark template
            'data' => [
                'url' => $data['final_url'],
                'original_url' => $data['url'],
                'title' => $data['title'],
                'description' => $data['description'],
                'thumbnail_url' => $data['thumbnail_url'],
                'icon_url' => $data['icon_url'],
                'site' => $data['site_url'],
            ],
        ]);
    }

    private function imageToHtml(Node $node, ?Blog $blog): string
    {
        /** @var string $src */
        $src = $node->attr('src') ?? '';
        /** @var string $alt */
        $alt = $node->attr('alt') ?? '';
        /** @var string $widthAttr */
        $widthAttr = $node->attr('width') ?? '';
        /** @var string $heightAttr */
        $heightAttr = $node->attr('height') ?? '';

        $srcset = null;
        $mediaName = $blog ? $this->getMediaNameFromPermalink($blog, $src) : null;

        if (
            $blog &&
            $mediaName &&
            ($media = $this->mediaService->getMediaByBlogAndName($blog, $mediaName)) &&
            $media->getExtension()
        ) {
            $mimeType = MimeTypes::getMimeFromExtension($media->getExtension());

            // TODO: image width should be pre-stored
            if ($this->imageResizeService->isMimeTypeSupported($mimeType)) {
                $contents = $this->mediaService->getContents($media);
                if ($contents !== null) {
                    $width = $this->imageResizeService->getImageWidth($contents);

                    $srcset = $src . ' ' . $width . 'w';

                    if ($width > 500) {
                        $srcset .= ', ' . $src . '/500w 500w';
                    }
                    if ($width > 750) {
                        $srcset .= ', ' . $src . '/750w 750w';
                    }
                    if ($width > 1000) {
                        $srcset .= ', ' . $src . '/1000w 1000w';
                    }
                    if ($width > 1500) {
                        $srcset .= ', ' . $src . '/1500w 1500w';
                    }
                }
            }
        }

        return '<img' .
            " src=\"$src\"" .
            ' loading="lazy"' .
            ($alt ? " alt=\"$alt\"" : '') .
            ($widthAttr ? " width=\"$widthAttr\"" : '') .
            ($heightAttr ? " height=\"$heightAttr\"" : '') .
            ($srcset ? " srcset=\"$srcset\"" : '') .
            '>';
    }

    private function getMediaNameFromPermalink(Blog $blog, string $permalink): ?string
    {
        $blogUrl = $this->permalinkService->getBlogUrl($blog);
        $path = str_replace($blogUrl, '', $permalink);
        $path = trim($path, '/');
        $split = explode('/', $path);

        if ($split[0] !== 'media') {
            return null;
        }

        return $split[1] ?? null;
    }

    private function tableCellToHtml(Node $node, string $children): string
    {
        $tag = $node->type instanceof Nodes\Table\TableCell\TableHeader ? 'th' : 'td';

        $attrs = [];
        /** @var int|string $rawColspan */
        $rawColspan = $node->attr('colspan');
        $colspan = intval($rawColspan);
        /** @var int|string $rawRowspan */
        $rawRowspan = $node->attr('rowspan');
        $rowspan = intval($rawRowspan);
        $colWidth = $node->attr('colwidth');

        if (is_array($colWidth)) {
            $width = array_sum($colWidth);
            $attrs['style'] = 'width: ' . $width . 'px;';
        }

        if ($colspan > 1) {
            $attrs['colspan'] = $colspan;
        }
        if ($rowspan > 1) {
            $attrs['rowspan'] = $rowspan;
        }

        $attrsString = '';
        if (count($attrs) > 0) {
            $attrsString = ' ' . implode(' ', array_map(function ($key, $value) {
                return "$key=\"$value\"";
            }, array_keys($attrs), array_values($attrs)));
        }

        return "<$tag$attrsString>$children</$tag>";
    }

    private function linkToHtml(Mark $mark, string $children, ?Blog $blog): string
    {
        /** @var string $href */
        $href = $mark->attr('href');

        $isInternal = $blog && $this->isLinkInternal($blog, $href);
        $rel = $this->getLinkRel(
            $isInternal || $blog->getMeta()->seo_external_links_follow === SeoExternalLinksFollow::FOLLOW
        );

        $target = $isInternal ? '' : ' target="_blank"';

        return "<a href=\"$href\"$target rel=\"$rel\">$children</a>";
    }

    private function isLinkInternal(Blog $blog, string $href): bool
    {
        if (!preg_match('/^https?:\/\//', $href)) {
            return true;
        }

        $blogUrl = $this->permalinkService->getBlogUrl($blog);
        $blogDomain = parse_url($blogUrl, PHP_URL_HOST);
        $hrefDomain = parse_url($href, PHP_URL_HOST);

        return $blogDomain === $hrefDomain;
    }

    /**
     * All links has the noopener and noreferrer privacy options
     * noopener - https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types/noopener
     * noreferrer - https://developer.mozilla.org/en-US/docs/Web/HTML/Link_types/noreferrer
     *
     * No follow is added based on blog settings
     */
    private function getLinkRel(bool $linksFollow): string
    {
        return 'noopener noreferrer' . ($linksFollow ? '' : ' nofollow');
    }
}
