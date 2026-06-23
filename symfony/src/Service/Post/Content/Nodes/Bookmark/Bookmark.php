<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Bookmark;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Post\Content\UrlDataService;
use App\Service\Theme\ThemeFilesService;
use App\Service\Delivery\Twig\TwigRendererService;
use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Bookmark extends NodeType
{
    public string $name = 'bookmark';
    public string $attrs = BookmarkAttrs::class;
    public string $group = 'block';

    public function __construct(
        private Blog $blog,
        private UrlDataService $urlDataService,
        private ThemeFilesService $themeFilesService,
        private TwigRendererService $twigRendererService,
        private string $projectDir,
    ) {}

    public function toHtml(Node $node, string $children): string
    {
        /** @var string $url */
        $url = $node->attr('url');

        if (!$url) {
            return '';
        }

        $data = $this->urlDataService->getLink((string) $url);

        if ($data === null) {
            return '';
        }

        $template = $this->themeFilesService->getFile(
            $this->blog,
            'node-bookmark.twig',
            ThemeFileFolder::TEMPLATES
        )?->getContent();

        if (!$template) {
            $template = strval(file_get_contents($this->projectDir . '/resources/twig/blocks/bookmark.twig'));
        }

        return $this->twigRendererService->renderString($template, [
            'data' => $data,
        ]);
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'a',
                getAttrs: function (DOMElement $node) {
                    if ($node->getAttribute('class') !== 'bookmark') {
                        return false;
                    }

                    if (!$node->getAttribute('data-url')) {
                        return false;
                    }

                    return BookmarkAttrs::fromArray([
                        'url' => $node->getAttribute('data-url'),
                    ]);
                }
            ),
        ];
    }
}
