<?php declare(strict_types=1);

namespace App\Service\Post\Content\Marks;

use App\Entity\Blog;
use App\Entity\Enum\Blog\SeoExternalLinksFollow;
use App\Service\Route\PermalinkService;
use DOMElement;
use Hyvor\Phrosemirror\Converters\HtmlParser\ParserRule;
use Hyvor\Phrosemirror\Document\Mark;
use Hyvor\Phrosemirror\Types\MarkType;

class Link extends MarkType
{
    public string $name = 'link';
    public string $attrs = LinkAttrs::class;

    public function __construct(
        private ?Blog $blog,
        private PermalinkService $permalinkService,
    ) {}

    public function toHtml(Mark $mark, string $children): string
    {
        /** @var string $href */
        $href = $mark->attr('href');

        $isInternal = $this->blog && $this->isLinkInternal($href);
        $rel = $this->getLinkRel(
            $isInternal || $this->blog->getMeta()->seo_external_links_follow === SeoExternalLinksFollow::FOLLOW
        );

        $target = $isInternal ? '' : ' target="_blank"';

        return "<a href=\"$href\"$target rel=\"$rel\">$children</a>";
    }

    public function fromHtml(): array
    {
        return [
            new ParserRule(
                tag: 'a',
                getAttrs: function (DOMElement $node): LinkAttrs|bool {
                    $href = $node->getAttribute('href');

                    if (!$href) {
                        return false;
                    }

                    return LinkAttrs::fromArray(['href' => $href]);
                }
            ),
        ];
    }

    private function isLinkInternal(string $href): bool
    {
        if (!preg_match('/^https?:\/\//', $href)) {
            return true;
        }

        $blogUrl = $this->permalinkService->getBlogUrl($this->blog);
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
