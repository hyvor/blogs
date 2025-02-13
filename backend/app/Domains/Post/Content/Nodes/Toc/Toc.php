<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Toc;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Theme\ThemeFilesRepository;
use App\Models\Blog;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Types\NodeType;
use Hyvor\Phrosemirror\Converters\HtmlSerializer\Context;

class Toc extends NodeType
{

    public const DEFAULT_LEVELS = [1,2,3,4,5,6];

    public string $name = 'toc';
    public ?string $content = null;
    public string $group = 'block';
    public string $attrs = TocAttrs::class;

    public function __construct(private Blog $blog)
    {}

    public function toHtmlFromContext(Context $context): string
    {

        $template = ThemeFilesRepository::getFile(
            $this->blog,
            'node-toc.twig',
            ThemeFileFolderEnum::TEMPLATES
        )?->content;

        if (!$template) {
            $template = '{{ toc | raw }}';
        }

        /** @var int[] $levels */
        $levels = $context->node->attrs->get('levels') ?: self::DEFAULT_LEVELS;
        $tocHtml = new TocHtml($levels);
        $toc = $tocHtml->htmlFromNode($context->topNode);

        return TwigRenderer::renderString($template, [
            'toc' => $toc
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
                        'levels' => $levels
                    ]);
                }
            )
        ];
    }

}