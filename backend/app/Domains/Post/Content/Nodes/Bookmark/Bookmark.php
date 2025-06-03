<?php declare(strict_types=1);

namespace App\Domains\Post\Content\Nodes\Bookmark;

use App\Data\Enums\ThemeFileFolderEnum;
use App\Data\Enums\UrlDataFetchTypeEnum;
use App\Data\Objects\ConsoleAPI\UrlDataObject;
use App\Domains\Delivery\Twig\TwigRenderer;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Theme\ThemeFilesRepository;
use App\Domains\UrlData\UrlDataRepository;
use App\Models\Blog;
use DOMElement;
use Exception;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Node;
use Hyvor\Phrosemirror\Types\NodeType;

class Bookmark extends NodeType
{
    public string $name = "bookmark";

    public string $attrs = BookmarkAttrs::class;
    public string $group = "block";

    public function __construct(public Blog $blog) {}

    public function toHtml(Node $node, string $children): string
    {
        $blog = $this->blog;

        $url = $node->attr("url");

        if (!$url) {
            return "";
        }

        try {
            $unfolded = app(UrlDataRepository::class)->fetch(
                $url,
                UrlDataFetchTypeEnum::LINK
            );
        } catch (Exception) {
            return "";
        }

        $template = ThemeFilesRepository::getFile(
            $blog,
            "node-bookmark.twig",
            ThemeFileFolderEnum::TEMPLATES
        )?->content;

        if (!$template) {
            $template = PostContentService::getDefaultBlockTemplate("bookmark");
        }

        return TwigRenderer::renderString($template, [
            "data" => UrlDataObject::fromUnfolded($unfolded),
        ]);
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: "a",
                getAttrs: function (DOMElement $node) {
                    if ($node->getAttribute("class") !== "bookmark") {
                        return false;
                    }

                    if (!$node->getAttribute("data-url")) {
                        return false;
                    }

                    return BookmarkAttrs::fromArray([
                        "url" => $node->getAttribute("data-url"),
                    ]);
                }
            ),
        ];
    }
}
