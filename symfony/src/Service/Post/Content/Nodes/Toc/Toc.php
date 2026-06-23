<?php declare(strict_types=1);

namespace App\Service\Post\Content\Nodes\Toc;

use App\Entity\Blog;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\ThemeFilesService;
use App\Service\Delivery\Twig\TwigRendererService;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Converters\HtmlSerializer\Context;
use Hyvor\Phrosemirror\Types\NodeType;

class Toc extends NodeType
{
    public const DEFAULT_LEVELS = [1, 2, 3, 4, 5, 6];

    public string $name = 'toc';
    public ?string $content = null;
    public string $group = 'block';
    public string $attrs = TocAttrs::class;

    public function __construct(
        private Blog $blog,
        private ThemeFilesService $themeFilesService,
        private TwigRendererService $twigRendererService,
    ) {}

    public function toHtmlFromContext(Context $context): string
    {
        $template = $this->themeFilesService->getFile(
            $this->blog,
            'node-toc.twig',
            ThemeFileFolder::TEMPLATES
        )?->getContent();

        if (!$template) {
            $template = '{{ toc | raw }}';
        }

        /** @var int[] $levels */
        $levels = $context->node->attrs->get('levels') ?: self::DEFAULT_LEVELS;
        $tocHtml = new TocHtml($levels);
        $toc = $tocHtml->htmlFromNode($context->topNode);

        return $this->twigRendererService->renderString($template, [
            'toc' => $toc,
        ]);
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'div',
                getAttrs: function ($node) {
                    if ($node->getAttribute('class') !== 'toc') {
                        return false;
                    }

                    $levels = $node->getAttribute('data-levels');
                    $levels = @explode(',', $levels);
                    $levels = array_map('intval', $levels);

                    return TocAttrs::fromArray([
                        'levels' => $levels,
                    ]);
                }
            ),
        ];
    }
}
